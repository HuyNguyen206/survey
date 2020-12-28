<?php $transFile='surveyError'; ?>
@if ($errors->any())
<div class="col-xs-12 fix-pad form-info-customer display-block bg-f2dede">
    <div class="col-xs-12 div-title bg-f2546d">{{trans($transFile.'.surveyError')}}</div>
    <div class="col-xs-12 fix-pad">
        <div class="col-xs-12 fix-pad border-gray">
            <div class="alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif