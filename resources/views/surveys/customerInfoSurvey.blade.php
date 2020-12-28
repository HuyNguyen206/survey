<?php $transFile = 'customerInfo'; ?>
<div>
    <div class="form-info-customer display-block bg-gray ">
        <div class="col-xs-12 div-title bg-blue"><span>{{trans($transFile.'.CustomerInformation')}}</span></div>
        <div class="col-xs-12 padding-10">
            <div class="w-input">
                <p>{{trans($transFile.'.Customer')}}</p>
                <input  type="text" class="form-control" value="{{ isset($accountInfo['CustomerName']) ? $accountInfo['CustomerName'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Phone')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['Phone']) ? $accountInfo['Phone'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Email')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['Email']) ? $accountInfo['Email'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.IDCard')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['Passport']) ? $accountInfo['Passport'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.DateOfBirth')}}</p>
                <input type="text" class="form-control"  value="{{ isset($accountInfo['Birthday']) ? $accountInfo['Birthday'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Company')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['CompanyName']) ? $accountInfo['CompanyName'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.InstallAddress')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['Address']) ? $accountInfo['Address'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.ContractNumber')}}</p>
                <input type="text" class="form-control"  value="{{ isset($accountInfo['ContractNum']) ? $accountInfo['ContractNum'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.DateContract')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['ContractDate']) ? $accountInfo['ContractDate'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.TypeOfContract')}}</p>
                <input class="form-control" type="text" value="{{ isset($accountInfo['ContractTypeName']) ? $accountInfo['ContractTypeName'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Status')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['ContractStatusName']) ? $accountInfo['ContractStatusName'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.UserName')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['UserName']) ? $accountInfo['UserName'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.PaymentType')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['PaymentType']) ? $accountInfo['PaymentType'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Brand')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['Location']) ? $accountInfo['Location'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.SaleAccount')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['AccountSale']) ? $accountInfo['AccountSale'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.InstallationAccount')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['AccountINF']) ? $accountInfo['AccountINF'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.SIRAccount')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['AccountListTIN']) ? $accountInfo['AccountListTIN'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.OnsiteAccount')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['AccountListINDO']) ? $accountInfo['AccountListINDO'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.AgentContact')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['contactPerson']) ? $accountInfo['contactPerson'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.FinishedMaintenance')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['FinishDateList'])? $accountInfo['FinishDateList'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.AccountPayment')}}</p>
                <input type="text" class="form-control" value="{{ isset($accountInfo['AccountPayment']) ? $accountInfo['AccountPayment'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.Bandwidth')}}</p>
                <input class="form-control"  type="text" readonly="true" style="color: red !important" value="{{ isset($accountInfo['Bandwidth']) ? $accountInfo['Bandwidth'] : null}}" readonly>
            </div>
            <div class="w-input">
                <p>{{trans($transFile.'.UseService')}}</p>
                <input type="checkbox" name="isp" class='isp'
                       @if (isset($accountInfo['UseService']) && ($accountInfo['UseService'] == 2 || $accountInfo['UseService'] == 3))
                       checked="true"
                       @else
                       checked="false"
                       @endif
                       title="" disabled>
                {{trans($transFile.'.Internet')}}
            </div>
        </div>
    </div>
    <span style="position: fixed; text-align: center; z-index: 99999" class='col-md-12'us-spinner="{radius:30, width:8, length: 16}"></span>
</div>

<div  class="center" style="display: none" id="loading-image"><img src="{{asset("assets/img/bluespinner.gif")}}" /></div>

<style>
     #loading-image {
                z-index: 99999;
                position: fixed;
                top: 50%;
                left: 50%;
                /* bring your own prefixes */
                transform: translate(-50%, -50%);
            }
</style>


   