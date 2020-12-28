<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Thông tin :attribute phải được chấp nhận.',
    'active_url'           => 'Thông tin :attribute không phải là đường dẫn URL hợp lệ.',
    'after'                => 'Thông tin :attribute phải là một ngày sau :date.',
    'alpha'                => 'Thông tin :attribute chỉ chứa chữ.',
    'alpha_dash'           => 'Thông tin :attribute chỉ chứa chữ, số và dấu gạch.',
    'alpha_num'            => 'Thông tin :attribute chỉ chứa chữ và số.',
    'array'                => 'Thông tin :attribute phải có dạng mảng.',
    'before'               => 'Thông tin :attribute phải là một ngày trước :date.',
    'between'              => [
        'numeric' => 'Thông tin :attribute phải ở giữa :min và :max.',
        'file'    => 'Thông tin :attribute phải ở giữa :min và :max kilobytes.',
        'string'  => 'Thông tin :attribute phải ở giữa :min và :max ký tự.',
        'array'   => 'Thông tin :attribute phải ở giữa :min và :max phần tử.',
    ],
    'boolean'              => 'Thông tin :attribute phải có dạng đúng hoặc sai.',
    'confirmed'            => 'Thông tin :attribute xác nhận không chính xác.',
    'date'                 => 'Thông tin :attribute không phải là một ngày hợp lệ.',
    'date_format'          => 'Thông tin :attribute không phù hợp với định dạng :format.',
    'different'            => 'Thông tin :attribute và :other phải khác nhau.',
    'digits'               => 'Thông tin :attribute phải là :digits số.',
    'digits_between'       => 'Thông tin :attribute phải ở giữa :min và :max số.',
    'email'                => 'Thông tin :attribute phải là địa chỉ hợp lệ.',
    'exists'               => 'Thông tin :attribute được chọn không tồn tại.',
    'filled'               => 'Thông tin :attribute không được để trống.',
    'image'                => 'Thông tin :attribute phải có định dạng ảnh.',
    'in'                   => 'Thông tin :attribute được chọn không hợp lệ.',
    'integer'              => 'Thông tin :attribute phải là số nguyên.',
    'ip'                   => 'Thông tin :attribute phải có định dạng IP hợp lệ.',
    'json'                 => 'Thông tin :attribute phải có định dạng là một chuỗi JSON.',
    'max'                  => [
        'numeric' => 'Thông tin :attribute không được lớn hơn :max.',
        'file'    => 'Thông tin :attribute không được lớn hơn :max kilobytes.',
        'string'  => 'Thông tin :attribute không được lớn hơn :max ký tự.',
        'array'   => 'Thông tin :attribute không được lớn hơn :max phần tử.',
    ],
    'mimes'                => 'Thông tin :attribute phải là một file có định dạng: :values.',
    'min'                  => [
        'numeric' => 'Thông tin :attribute không được nhỏ hơn :min.',
        'file'    => 'Thông tin :attribute không được nhỏ hơn :min kilobytes.',
        'string'  => 'Thông tin :attribute không được nhỏ hơn :min ký tự.',
        'array'   => 'Thông tin :attribute không được nhỏ hơn :min phần tử.',
    ],
    'not_in'               => 'Thông tin :attribute được chọn không hợp lệ.',
    'numeric'              => 'Thông tin :attribute phải là số.',
    'regex'                => 'Thông tin :attribute định dạng không hợp lệ.',
    'required'             => 'Thông tin :attribute không được bỏ trống.',
    'required_if'          => 'Thông tin :attribute cần phải có khi :other là :value.',
    'required_unless'      => 'Thông tin :attribute cần phải có chỉ khi :other ở trong :values.',
    'required_with'        => 'Thông tin :attribute cần phải có khi :values hiện diện.',
    'required_with_all'    => 'Thông tin :attribute cần phải có khi :values hiện diện.',
    'required_without'     => 'Thông tin :attribute cần phải có khi :values không có.',
    'required_without_all' => 'Thông tin :attribute cần phải có khi không có bất kỳ :values hiện diện.',
    'same'                 => 'Thông tin :attribute và :other phải trùng nhau.',
    'size'                 => [
        'numeric' => 'Thông tin :attribute phải là :size.',
        'file'    => 'Thông tin :attribute phải là :size kilobytes.',
        'string'  => 'Thông tin :attribute phải là :size ký tự.',
        'array'   => 'Thông tin :attribute phải chứa :size phần tử.',
    ],
    'string'               => 'Thông tin :attribute phải là một chuỗi.',
    'timezone'             => 'Thông tin :attribute phải hợp lệ.',
    'unique'               => 'Thông tin :attribute đã tồn tại.',
    'url'                  => 'Thông tin :attribute có định dạng không hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
		'email' => 'thư điện tử',
		'password' => 'mật khẩu',
		'name' => 'tên',
		'description' => 'mô tả',
	],

    'TheAgentDidNotChooseAnyAnswerYet' => "Nhân viên chưa chọn câu trả lời nào cho câu hỏi số :questionNumber",
    'TheAgentMustFillNoteForBadAnswerCustomer' => 'Nhân viên phải điền vào ô ghi chú cho câu đánh giá không hài lòng ở câu hỏi số :questionNumber',
    'TheAgentMustChooseResolveForBadAnswerCustomer' => 'Nhân viên phải chọn hành động xử lý cho câu đánh giá không hài lòng ở câu hỏi số :questionNumber',
    'TheAgentMustChooseErrorTypeForBadAnswerCustomer' => 'Nhân viên phải chọn loại lỗi cho câu đánh giá không hài lòng ở câu hỏi số :questionNumber',
    'Contact' => 'Thiếu thông tin liên hệ',
];
