<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SurveySections;
//use Illuminate\Support\Facades\DB;
use App\Models\CheckList;
use App\Models\PrecheckList;
use App\Models\FowardDepartment;
use App\Models\Apiisc;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Facades\Redis;
use DB;

class UpdateChecklistFromISC extends Controller {

    //Cập nhập dữ liệu checklist
    public function updateChecklist() {
        try {
//                DB::enableQueryLog();
            $listCLID = [];
            $checklist_update_later = Redis::get('checklist_update_later');
            if ($checklist_update_later != null) {
                $arrayDayUpdateData = json_decode($checklist_update_later);
                //Ngày mới
                if (date('y-m-d') != $arrayDayUpdateData->time) {
                    Redis::del('checklist_update_later');
                } else {
                    $listCLID = $arrayDayUpdateData->listCL;
                }
            }
            $listCL = DB::table('checklist')
                    ->select('id_checklist_isc')
                    ->where(function($query) {
                        $query->whereNotNull('id_checklist_isc');
                        $query->where('id_checklist_isc', '<>', 0);
                    })
                    ->where(function($query) {
                        $query->whereNotIn('final_status_id', [1, 97, 98, 99, 100, 11, 3]);
                        $query->WhereNotNull('final_status_id');
                    })
                    ->where(function($query) use ($listCLID) {
                        if (!empty($listCLID))
                            $query->whereNotIn('id_checklist_isc', $listCLID);
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
//                    ->offset($offset * 20)
//                    ->tosql();
                    ->get();
//                    dump(DB::getQueryLog());die;
            $TotalCl = count($listCL);
            //Có dữ liệu cần cập nhập
            if ($TotalCl > 0) {
                $listCLID = [];
                for ($i = 0; $i <= $TotalCl - 1; $i++) {
                    array_push($listCLID, $listCL[$i]->id_checklist_isc);
                }
                $listCL_update_later = $this->updateCLData($listCLID);
                if (!empty($listCL_update_later)) {
                    $checklist_update_later = Redis::get('checklist_update_later');
                    //Ngày mới hoặc lần đầu chạy
                    if ($checklist_update_later == null) {
                        Redis::set('checklist_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $listCL_update_later]));
                    }
                    //Ngày cũ
                    else {
                        $DayUpdateData = json_decode($checklist_update_later);
                        $addListCLData = array_merge($DayUpdateData->listCL, $listCL_update_later);
                        Redis::set('checklist_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $addListCLData]));
                    }
                }
//                if($result)
//                return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập Checklist thành công']);
//                else
            }
            return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Dữ liệu Checklist đã được cập nhập đầy đủ ']);
        } catch (Exception $e) {
            return json_encode(['code' => 500, 'status' => 'Lỗi', 'msg' => $e->getMessage()]);
        }
    }

//Gọi qua api ISC để cập nhập dữ liệu checklist
    public function updateCLData($listCLID) {
        try {
            $listCL_update_later = [];
            $listCLIDString = implode(',', $listCLID);
            $listCLIDString = array('ChecklistID' => $listCLIDString
            );
            $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
            $uri .= http_build_query($listCLIDString);
            $apiISC = new Apiisc();
            $resultSetClData = json_decode($apiISC->getAPI($uri));

            if ($resultSetClData->statusCode == 200) {

                $checklistUpdate = new CheckList();
                foreach ($resultSetClData->data as $key => $value) {
                    $checklistUpdate = CheckList::where('id_checklist_isc', '=', $value->Id)->get();
//                    if ($checklistUpdate->final_status_id != 1 && $checklistUpdate->final_status_id != 97) {
                    foreach ($checklistUpdate as $key2 => $value2) {
                        //Cần cập nhập
                        if (in_array($value->Final_Status_Id, [1, 97, 98, 99, 100, 11, 3])) {
//                            $updatable = true;
                            $value2->final_status = isset($value->Final_Status) ? $value->Final_Status : NULL;
                            $value2->final_status_id = isset($value->Final_Status_Id) ? $value->Final_Status_Id : NULL;
                            $value2->total_minute = isset($value->TongSoPhut) ? $value->TongSoPhut : NULL;
                            $value2->input_time = isset($value->ThoiGianNhap) ? $value->ThoiGianNhap : NULL;
                            $value2->assign = isset($value->Phancong) ? $value->Phancong : NULL;
                            $value2->store_time = isset($value->ThoiGianTon) ? $value->ThoiGianTon : NULL;
                            $value2->error_position = isset($value->ViTriLoi) ? $value->ViTriLoi : NULL;
                            $value2->error_description = isset($value->MotaLoi) ? $value->MotaLoi : NULL;
                            $value2->reason_description = isset($value->NguyenNhan) ? $value->NguyenNhan : NULL;
                            $value2->way_solving = isset($value->HuongXuLy) ? $value->HuongXuLy : NULL;
                            $value2->checklist_type = isset($value->LoaiCl) ? $value->LoaiCl : NULL;
                            $value2->repeat_checklist = isset($value->CLlap) ? $value->CLlap : NULL;
                            $value2->finish_date = isset($value->FinishDate) ? $value->FinishDate : NULL;
                            $value2->save();
                        } else {
                            if (!in_array($value->Id, $listCL_update_later)) {
                                array_push($listCL_update_later, $value->Id);
                            }
                        }
                    }
                }
                return $listCL_update_later;

//                    }
            }
        } catch (Exception $e) {
            $logger = new Logger('my_logger');
            $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_Update_Checklist_Error.log', Logger::INFO));
            $logger->addInfo('Log Call API', array('TimeStartCall' => new \DateTime(), 'input' => $uri, 'output' => $resultSetClData, 'error' => $e->getMessage()));
        }
    }

    //Cập nhập dữ liệu Prechecklist
    public function updatePrechecklist() {
        try {
            $listPCLID = [];
            $prechecklist_update_later = Redis::get('prechecklist_update_later');
            if ($prechecklist_update_later != null) {
                $arrayDayUpdateData = json_decode($prechecklist_update_later);
                //Ngày mới
                if (date('y-m-d') != $arrayDayUpdateData->time) {
                    Redis::del('prechecklist_update_later');
                } else {
                    $listPCLID = $arrayDayUpdateData->listPCL;
                }
            }
            $listPCL = DB::table('prechecklist')
                    ->select('id_prechecklist_isc')
                    ->where(function($query) {
                        $query->where('id_prechecklist_isc', '<>', 0);
                        $query->whereNotNull('id_prechecklist_isc');
                    })
                    ->where(function($query) {
                        $query->where('sup_status_id', '<>', 3);
                    })
                    ->where(function($query) use ($listPCLID) {
                        if (!empty($listPCLID))
                            $query->whereNotIn('id_prechecklist_isc', $listPCLID);
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
                    ->get();
            $TotalPCl = count($listPCL);
            //Có dữ liệu cần cập nhập
            if ($TotalPCl > 0) {
                $listPCLID = [];
                for ($i = 0; $i <= $TotalPCl - 1; $i++) {
                    array_push($listPCLID, $listPCL[$i]->id_prechecklist_isc);
                }

                $listPCL_update_later = $this->updatePCLData($listPCLID);
                if (!empty($listPCL_update_later)) {
                    $prechecklist_update_later = Redis::get('prechecklist_update_later');
                    //Ngày mới hoặc lần đầu chạy
                    if ($prechecklist_update_later == null) {
                        Redis::set('prechecklist_update_later', json_encode(['time' => date('y-m-d'), 'listPCL' => $listPCL_update_later]));
                    }
                    //Ngày cũ
                    else {
                        $DayUpdateData = json_decode($prechecklist_update_later);
                        $addListPCLData = array_merge($DayUpdateData->listPCL, $listPCL_update_later);
                        Redis::set('prechecklist_update_later', json_encode(['time' => date('y-m-d'), 'listPCL' => $addListPCLData]));
                    }
                }
//                return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập Prechecklist thành công']);
            }
            return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Dữ liệu Prechecklist đã được cập nhập đầy đủ ']);
        } catch (Exception $e) {
            return json_encode(['code' => 500, 'status' => 'Lỗi', 'msg' => $e->getMessage()]);
        }
    }

    //Gọi qua api ISC để cập nhập dữ liệu Prechecklist
    public function updatePCLData($listPCLID) {
        try {
            $listPCL_update_later = [];
            $listPCLIDString = implode(',', $listPCLID);
            $listPCLIDString = array('IDPreCheckList' => $listPCLIDString
            );
            $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetPreChecklistByIDPreCheckList?';
            $uri .= http_build_query($listPCLIDString);
            $apiISC = new Apiisc();
            $resultSetPClData = json_decode($apiISC->getAPI($uri));
            $PrechecklistUpdate = new PrecheckList();
            $listSupIDPartner = [];
            foreach ($resultSetPClData as $key => $value) {
                $PrechecklistUpdate = PrecheckList::where('id_prechecklist_isc', '=', $value->ID)->get();
                foreach ($PrechecklistUpdate as $key2 => $value2) {
                    if ($value->SupStatus == 3) {
                        $value2->sup_status_id = isset($value->SupStatus) ? $value->SupStatus : NULL;
                        $value2->appointment_timer = (isset($value->AppointmentTimer) && $value->AppointmentTimer != NULL && date_create($value->AppointmentTimer) != false) ? date_format(date_create($value->AppointmentTimer), "Y-m-d H:i:s") : NULL;
                        $value2->count_sup = isset($value->CountSup) ? $value->CountSup : NULL;
                        $value2->total_minute = isset($value->TotalMinute) ? $value->TotalMinute : NULL;
                        $value2->action_process = isset($value->ActionProcess) ? $value->ActionProcess : NULL;
                        $value2->update_date = isset($value->UpdateDate) ? $value->UpdateDate : NULL;
                        if ($value->SupIDPartner != null) {
                            array_push($listSupIDPartner, $value->SupIDPartner);
                        }
                        $value2->sup_id_partner = isset($value->SupIDPartner) ? $value->SupIDPartner : NULL;

                        $value2->save();
                    } else {
                        if (!in_array($value->ID, $listPCL_update_later)) {
                            array_push($listPCL_update_later, $value->ID);
                        }
                    }
                }
            }

            //Có checklist phát sinh
            if (!empty($listSupIDPartner)) {
                $listCLIDString = implode(',', $listSupIDPartner);
                $listCLIDString = array('ChecklistID' => $listCLIDString
                );
                $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
                $uri .= http_build_query($listCLIDString);
                $apiISC = new Apiisc();
                $resultSetClData = json_decode($apiISC->getAPI($uri));
                if ($resultSetClData->statusCode == 200) {
                    foreach ($resultSetClData->data as $key => $value) {
                        $result = DB::table('checklist')->select('id_checklist_isc')->where('id_checklist_isc', $value->Id)->get();
                        $checklist = Checklist::where('id_checklist_isc', '=', $value->Id)->first();
                        //Nếu chưa tồn tạo thì tạo mới
                        if ($checklist == NULL)
                            $checklist = new CheckList();
                        $contractTypeSection = DB::table('prechecklist')->select('section_survey_id', 'section_code', 'section_contract_num')
                                        ->where('sup_id_partner', '=', $value->Id)->get();
                        $checklist->id_checklist_isc = $value->Id;
                        $checklist->section_survey_id = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_survey_id : null;
                        $checklist->section_code = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_code : null;
                        $checklist->section_contract_num = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_contract_num : null;
                        $checklist->final_status = isset($value->Final_Status) ? $value->Final_Status : NULL;
                        $checklist->final_status_id = isset($value->Final_Status_Id) ? $value->Final_Status_Id : NULL;
                        $checklist->total_minute = isset($value->TongSoPhut) ? $value->TongSoPhut : NULL;
                        $checklist->input_time = isset($value->ThoiGianNhap) ? $value->ThoiGianNhap : NULL;
                        $checklist->assign = isset($value->Phancong) ? $value->Phancong : NULL;
                        $checklist->store_time = isset($value->ThoiGianTon) ? $value->ThoiGianTon : NULL;
                        $checklist->error_position = isset($value->ViTriLoi) ? $value->ViTriLoi : NULL;
                        $checklist->error_description = isset($value->MotaLoi) ? $value->MotaLoi : NULL;
                        $checklist->reason_description = isset($value->NguyenNhan) ? $value->NguyenNhan : NULL;
                        $checklist->way_solving = isset($value->HuongXuLy) ? $value->HuongXuLy : NULL;
                        $checklist->checklist_type = isset($value->LoaiCl) ? $value->LoaiCl : NULL;
                        $checklist->repeat_checklist = isset($value->CLlap) ? $value->CLlap : NULL;
                        $checklist->finish_date = isset($value->FinishDate) ? $value->FinishDate : NULL;
                        $checklist->save();
                    }
                }
            }
            return $listPCL_update_later;
        } catch (Exception $e) {
            $logger = new Logger('my_logger');
            $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_Update_Prechecklist_Error.log', Logger::INFO));
            $logger->addInfo('Log Call API', array('TimeStartCall' => new \DateTime(), 'input' => $uri, 'output' => $resultSetClData, 'error' => $e->getMessage()));
        }
    }

    //Cập nhập dữ liệu chuyển tiếp phòng ban
    public function updateFowardDepartment() {
        try {
//            DB::enableQueryLog();
            $listFDID = [];
            $fd_update_later = Redis::get('fd_update_later');
            if ($fd_update_later != null) {
                $arrayDayUpdateData = json_decode($fd_update_later);
                //Ngày mới
                if (date('y-m-d') != $arrayDayUpdateData->time) {
                    Redis::del('fd_update_later');
                } else {
                    $listFDID = $arrayDayUpdateData->listFD;
                }
            }
            $listFD = DB::table('foward_department')
                    ->select('foward_id')
                    ->where(function($query) {
                        $query->where('foward_id', '<>', 0);
                        $query->whereNotNull('foward_id');
                    })
                    ->where(function($query) {
                        $query->whereNotIn('status_id', [2]);
                    })
                    ->where(function($query) use ($listFDID) {
                        if (!empty($listFDID))
                            $query->whereNotIn('foward_id', $listFDID);
                    })
                    ->orderBy('created_at', 'ASC')
                    ->limit(20)
                    ->get();
//                    dump(DB::getQueryLog());die;
            $TotalFD = count($listFD);
            //Có dữ liệu cần cập nhập
            if ($TotalFD > 0) {
                $listFDID = [];
                for ($i = 0; $i <= $TotalFD - 1; $i++) {
                    array_push($listFDID, $listFD[$i]->foward_id);
                }
                $listFD_update_later = $this->updateFDData($listFDID);
                if (!empty($listFD_update_later)) {
                    $fd_update_later = Redis::get('fd_update_later');
                    //Ngày mới hoặc lần đầu chạy
                    if ($fd_update_later == null) {
                        Redis::set('fd_update_later', json_encode(['time' => date('y-m-d'), 'listFD' => $listFD_update_later]));
                    }
                    //Ngày cũ
                    else {
                        $DayUpdateData = json_decode($fd_update_later);
                        $addListFDData = array_merge($DayUpdateData->listFD, $listFD_update_later);
                        Redis::set('fd_update_later', json_encode(['time' => date('y-m-d'), 'listCL' => $addListFDData]));
                    }
                }

//                return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập FowardDepartment thành công']);
            }
            return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Dữ liệu FowardDepartment đã được cập nhập đầy đủ ']);
        } catch (Exception $e) {
            return json_encode(['code' => 500, 'status' => 'Lỗi', 'msg' => $e->getMessage()]);
        }
    }

    //Gọi qua api ISC để cập nhập dữ liệu chuyển tiếp phòng ban
    public function updateFDData($listFDID) {
        try {
              $listFD_update_later = [];
            $listFDIDString = implode(',', $listFDID);
            $listFDIDString = array('DiscussionID' => $listFDIDString
            );
            $uri = 'http://cemcc.fpt.net/wscustomerinfo.asmx/spCEM_GetCuscareDiscussionByID?';
            $uri .= http_build_query($listFDIDString);
            $apiISC = new Apiisc();
            $resultSetFDData = json_decode($apiISC->getAPI($uri));
            $FDUpdate = new FowardDepartment();
            foreach ($resultSetFDData as $key => $value) {
                $FDUpdate = FowardDepartment::where('foward_id', '=', $value->ID)->get();
                foreach ($FDUpdate as $key2 => $value2) {
                    if ($value->StatusID == 2) {
                        $value2->status_id = isset($value->StatusID) ? $value->StatusID : NULL;
                        $value2->create_date = isset($value->CreateDate) ? $value->CreateDate : NULL;
                        $value2->description = isset($value->Description) ? $value->Description : NULL;
                        $value2->department_transfer = isset($value->DepartmentTransfer) ? $value->DepartmentTransfer : NULL;
                        $value2->department = isset($value->Department) ? $value->Department : NULL;
                        $value2->content = isset($value->Content) ? $value->Content : NULL;
                        $value2->status = isset($value->Status) ? $value->Status : NULL;
                        $value2->update_by = isset($value->UpdateBy) ? $value->UpdateBy : NULL;
                        $value2->update_date = isset($value->UpdateDate) ? $value->UpdateDate : NULL;
                        $value2->total_minute = isset($value->TotalMinutes) ? $value->TotalMinutes : NULL;
                        $value2->save();
                    } else {
                        if (!in_array($value->ID, $listFD_update_later)) {
                            array_push($listFD_update_later, $value->ID);
                        }
                    }
                }
            }
            return $listFD_update_later;
        } catch (Exception $e) {
            $logger = new Logger('my_logger');
            $logger->pushHandler(new StreamHandler(storage_path() . '/logs/API_ISC_Update_FD_Error.log', Logger::INFO));
            $logger->addInfo('Log Call API', array('TimeStartCall' => new \DateTime(), 'input' => $uri, 'output' => $resultSetFDData, 'error' => $e->getMessage()));
        }
    }

    //Cập nhập dữ liệu checklist trong một lần gọi
    public function updateChecklistAtOnce(Request $request) {
        $listCL = DB::table('checklist')
                ->select('id_checklist_isc')
                ->where(function($query) {
                    $query->whereNotNull('id_checklist_isc');
                    $query->where('id_checklist_isc', '<>', 0);
                })
                ->where(function($query) {
                    $query->whereNotIn('final_status_id', [1, 97, 98, 99, 100, 11, 3]);
                    $query->WhereNotNull('final_status_id');
                })
                ->orderBy('created_at', 'ASC')
//                    ->limit(20)
//                    ->tosql();
                ->get();
        $TotalCl = count($listCL);
        //Nhiều hơn 1 lần gọi
        if ($TotalCl >= 20) {
            $sendRemain = $TotalCl % 20;
            $sendNumber = ($TotalCl - $sendRemain) / 20;
            $start = 0;
            for ($s = 1; $s <= $sendNumber; $s++) {
                $start = $this->updateCL($start, $listCL);
            }
            //Nếu dư ra số checklist id nhỏ hơn 20
            if ($sendRemain != 0) {
                $listCLID = [];
                for ($i = $start; $i <= $TotalCl - 1; $i++) {
                    array_push($listCLID, $listCL[$i]->id_checklist_isc);
                }
                $this->updateCLData($listCLID);
            }
        } else if ($TotalCl > 0) {
            $start = 0;
            $listCLID = [];
            for ($i = $start; $i <= $TotalCl - 1; $i++) {
                array_push($listCLID, $listCL[$i]->id_checklist_isc);
            }
            $this->updateCLData($listCLID);
        }
        return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập Checklist thành công']);
    }

    //Cập nhập dữ liệu checklist
    public function updateCL($start, $listCL) {
        $listCLID = [];
        for ($i = $start; $i <= $start + 19; $i++) {
            array_push($listCLID, $listCL[$i]->id_checklist_isc);
        }
        $this->updateCLData($listCLID);
        return $start = $start + 20;
    }

    //Cập nhập dữ liệu Prechecklist trong một lần gọi
    public function updatePrechecklistAtOnce(Request $request) {
        $listPCL = DB::table('prechecklist')
                ->select('id_prechecklist_isc')
                ->where('status', '<>', 3)
                ->where('id_prechecklist_isc', '<>', 0)
                ->whereNotNull('id_prechecklist_isc')
                ->get();
        $TotalPCl = count($listPCL);
        //Nhiều hơn 1 lần gọi
        if ($TotalPCl >= 20) {
            $sendRemain = $TotalPCl % 20;
            $sendNumber = ($TotalPCl - $sendRemain) / 20;
            $start = 0;
            for ($s = 1; $s <= $sendNumber; $s++) {
                $start = $this->updatePCL($start, $listPCL);
            }
            //Nếu dư ra số Prechecklist id nhỏ hơn 20
            if ($sendRemain != 0) {
                $listPCLID = [];
                for ($i = $start; $i <= $TotalPCl - 1; $i++) {
                    array_push($listPCLID, $listPCL[$i]->id_prechecklist_isc);
                }
                $this->updatePCLData($listPCLID);
            }
        } else if ($TotalPCl > 0) {
            $start = 0;
            $listPCLID = [];
            for ($i = $start; $i <= $TotalPCl - 1; $i++) {
                array_push($listPCLID, $listPCL[$i]->id_prechecklist_isc);
            }
            $this->updatePCLData($listPCLID);
        }
        return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập Prechecklist thành công']);
    }

    //Cập nhập dữ liệu Prechecklist
    public function updatePCL($start, $listPCL) {
        $listPCLID = [];
        for ($i = $start; $i <= $start + 19; $i++) {
            array_push($listPCLID, $listPCL[$i]->id_prechecklist_isc);
        }
        $this->updatePCLData($listPCLID);
        return $start = $start + 20;
    }

    //Cập nhập dữ liệu ForwardDepartment trong một lần gọi
    public function updateFDAtOnce(Request $request) {
        $listFD = DB::table('foward_department')
                ->select('foward_id')
                ->where(function($query) {
                    $query->where('foward_id', '<>', 0);
                })
                ->where(function($query) {
                    $query->whereNotIn('status_id', [2]);
                    $query->orWhereNull('status_id');
                })
                ->get();
        $TotalFD = count($listFD);
        //Nhiều hơn 1 lần gọi
        if ($TotalFD >= 20) {
            $sendRemain = $TotalFD % 20;
            $sendNumber = ($TotalFD - $sendRemain) / 20;
            $start = 0;
            for ($s = 1; $s <= $sendNumber; $s++) {
                $start = $this->updateFD($start, $listFD);
            }
            //Nếu dư ra số ForwardDepartment id nhỏ hơn 20
            if ($sendRemain != 0) {
                $listFDID = [];
                for ($i = $start; $i <= $TotalFD - 1; $i++) {
                    array_push($listFDID, $listFD[$i]->foward_id);
                }
                $this->updateFDData($listFDID);
            }
        } else if ($TotalFD > 0) {
            $start = 0;
            $listFDID = [];
            for ($i = $start; $i <= $TotalFD - 1; $i++) {
                array_push($listFDID, $listFD[$i]->foward_id);
            }
            $this->updateFDData($listFDID);
        }
        return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Cập nhập FD thành công']);
    }

    //Cập nhập dữ liệu ForwardDepartment
    public function updateFD($start, $listFD) {
        $listFDLID = [];
        for ($i = $start; $i <= $start + 19; $i++) {
            array_push($listFDLID, $listFD[$i]->foward_id);
        }
        $this->updateFDData($listFDLID);
        return $start = $start + 20;
    }

    public function insertCLData() {
        try {
            $listCLID = DB::table('prechecklist')
                    ->select('sup_id_partner')
                    ->where(function($query) {
                        $query->whereNotNull('sup_id_partner');
//                        $query->where('created_at', '>=', '2017-06-20 00:00:00');
//                        $query->where('created_at', '<=', '2017-06-21 23:59:59');
                    })
//                    ->tosql();
                    ->get();
            $arrayCLID = [];
            foreach ($listCLID as $key1 => $value1) {
                array_push($arrayCLID, $value1->sup_id_partner);
            }

//            $listCLIDString = implode(',', $arrayCLID);
            $listCLIDString = '1102317372,1102319502,1102319622,1102319712,1102320502,1102322152,1102323052,1102323312,1102324202,1102324442,1102328492,1102329782,1102776472,1103143102,1103223412,1103453712,1103455962,1103540962,1103548292,1103549142,1103553632,1103581052,1103815002,1103880562,1103924932,1103937542,1104038692,1104108112,1104147152,1104282202,1104317142,1104321742,1104322712,1104327942,1104329582,1104333912,1104346242,1104397042,1104399552,1104680472,1104777082,1104781692,1104956542,1105172982,1105194702,1105396462,1105584312,1105604572,1105753832,1105768112,1105931542,1105937962,1106115482,1106164632,1106172602,1106197362,1106217002,1106368632,1106518262,1106528122,1106536272';

            $listCLIDString = array('ChecklistID' => $listCLIDString
            );
            $uri = 'http://parapi.fpt.vn/api/RadAPI/SupportListGetByCLID/?';
            $uri .= http_build_query($listCLIDString);
            $apiISC = new Apiisc();
            $resultSetClData = json_decode($apiISC->getAPI($uri));
            if ($resultSetClData->statusCode == 200) {
                foreach ($resultSetClData->data as $key => $value) {
                    $checklist = Checklist::where('id_checklist_isc', '=', $value->Id)->first();
                    //Nếu chưa tồn tạo thì tạo mới
                    if ($checklist == NULL)
                        $checklist = new CheckList();
                    $contractTypeSection = DB::table('prechecklist')->select('section_survey_id', 'section_code', 'section_contract_num')
                                    ->where('sup_id_partner', '=', $value->Id)->get();
                    $checklist->id_checklist_isc = $value->Id;
                    $checklist->section_survey_id = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_survey_id : null;
                    $checklist->section_code = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_code : null;
                    $checklist->section_contract_num = isset($contractTypeSection[0]) ? $contractTypeSection[0]->section_contract_num : null;
                    $checklist->final_status = isset($value->Final_Status) ? $value->Final_Status : NULL;
                    $checklist->final_status_id = isset($value->Final_Status_Id) ? $value->Final_Status_Id : NULL;
                    $checklist->total_minute = isset($value->TongSoPhut) ? $value->TongSoPhut : NULL;
                    $checklist->input_time = isset($value->ThoiGianNhap) ? $value->ThoiGianNhap : NULL;
                    $checklist->assign = isset($value->Phancong) ? $value->Phancong : NULL;
                    $checklist->store_time = isset($value->ThoiGianTon) ? $value->ThoiGianTon : NULL;
                    $checklist->error_position = isset($value->ViTriLoi) ? $value->ViTriLoi : NULL;
                    $checklist->error_description = isset($value->MotaLoi) ? $value->MotaLoi : NULL;
                    $checklist->reason_description = isset($value->NguyenNhan) ? $value->NguyenNhan : NULL;
                    $checklist->way_solving = isset($value->HuongXuLy) ? $value->HuongXuLy : NULL;
                    $checklist->checklist_type = isset($value->LoaiCl) ? $value->LoaiCl : NULL;
                    $checklist->repeat_checklist = isset($value->CLlap) ? $value->CLlap : NULL;

                    $checklist->finish_date = isset($value->FinishDate) ? $value->FinishDate : NULL;
                    $checklist->save();
                }
            }
            return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Dữ liệu Checklist đã được cập nhập đầy đủ ']);
        } catch (Exception $e) {
            return json_encode(['code' => 500, 'status' => 'Lỗi', 'msg' => $e->getMessage()]);
        }
    }

    //Cập nhập dữ liệu chuyển tiếp phòng ban
//    public function updateFowardDepartment() {
//        return json_encode(['code' => 200, 'status' => 'Thành công', 'msg' => 'Đang cập nhập']);
//    }
}
