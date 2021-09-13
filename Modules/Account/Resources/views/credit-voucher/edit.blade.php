@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form id="credit-voucher-form" method="post">
                        @csrf
                        <div class="row">
                                <div class="form-group col-md-3 required">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $debit_voucher[0]->voucher_no }}" readonly />
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ $debit_voucher[0]->voucher_date }}" readonly />
                                </div>
                                <x-form.selectbox labelName="Debit Account Head" name="debit_account_id" required="required"  col="col-md-3" class="selectpicker">
                                    @if (!$debit_accounts->isEmpty())
                                    @foreach ($debit_accounts as $coa)
                                        @if(!empty($coa->bank_id) || !empty($coa->mobile_bank_id))
                                            @if($coa->bank_id)
                                                @if($coa->bank->warehouse_id == $warehouse_id)
                                                <option value="{{ $coa->id }}" {{ ($debit_voucher[0]->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                @endif
                                            @elseif($coa->mobile_bank_id)
                                                @if($coa->mobile_bank->warehouse_id == $warehouse_id)
                                                <option value="{{ $coa->id }}" {{ ($debit_voucher[0]->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                @endif
                                            @endif
                                        @else
                                        <option value="{{ $coa->id }}" {{ ($debit_voucher[0]->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                        @endif
                                        
                                    @endforeach
                                    @endif

                                </x-form.selectbox>
                                
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="debit-voucher-table">
                                        <thead class="bg-primary">
                                            <th width="60%">Account Name</th>
                                            <th width="30%" class="text-right">Amount</th>
                                            <th width="10%"></th>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total = 0;
                                            @endphp
                                            @if (!$credit_voucher->isEmpty())
                                                @foreach($credit_voucher as $key =>  $cvoucher)
                                                @php
                                                    $total += $cvoucher->credit;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <select name="credit_account[{{ $key + 1 }}][id]" id="credit_account_{{ $key + 1 }}_id" class="form-control selectpicker">
                                                            <option value="">Select Please</option>
                                                            @if (!$transactional_accounts->isEmpty())
                                                            @foreach ($transactional_accounts as $coa)
                                                                @if(!empty($coa->customer_id) || !empty($coa->bank_id) || !empty($coa->mobile_bank_id))
                                                                    @if($coa->customer_id)
                                                                            @if($coa->customer->district_id == $district_id)
                                                                            <option value="{{ $coa->id }}" {{ ($cvoucher->chart_of_account_id ==  $coa->id) ? 'selected' : '' }}>{{ $coa->name }} [Customer]</option>
                                                                            @endif    
                                                                    @elseif($coa->bank_id)
                                                                        @if($coa->bank->warehouse_id == $warehouse_id)
                                                                        <option value="{{ $coa->id }}" {{ ($cvoucher->chart_of_account_id ==  $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                        @endif
                                                                    @elseif($coa->mobile_bank_id)
                                                                        @if($coa->mobile_bank->warehouse_id == $warehouse_id)
                                                                        <option value="{{ $coa->id }}" {{ ($cvoucher->chart_of_account_id ==  $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                        @endif
                                                                    @endif
                                                                @else 
                                                                <option value="{{ $coa->id }}" {{ ($cvoucher->chart_of_account_id ==  $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                @endif
                                                                
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right amount" value="{{ $cvoucher->credit }}" onkeyup="calculate_total()" name="credit_account[{{ $key + 1 }}][amount]" id="credit_account_{{ $key + 1 }}_amount" placeholder="0.00">
                                                    </td>
                                                    @if ($key == 0)
                                                        <td></td>
                                                    @else
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                                                                data-placement="top" data-original-title="Remove">
                                                                <i class="fas fa-minus-square"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="text-right">Total</td>
                                                <td><input type="text" class="form-control text-right bg-primary text-white" value="{{ $total }}" name="grand_total" id="grand_total" placeholder="0.00" readonly></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-primary btn-sm" id="add_more_account"
                                                    data-toggle="tooltip" data-placement="top" data-original-title="Add More"><i class="fas fa-plus-square"></i></button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <x-form.textarea labelName="Remarks" name="remarks" value="{{ $credit_voucher[0]->description }}" col="col-md-12"/>
                                <div class="form-group col-md-12 pt-5 text-center">
                                    <a href="{{ url('voucher-approval') }}" type="button" class="btn btn-danger btn-sm mr-3"><i class="far fa-window-close"></i> Cancel</a>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-sync-alt"></i> Update</button>
                                </div>
                        </div>
                    </form>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>
$('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
@if(!$credit_voucher->isEmpty())
var count = "{{ count($credit_voucher) }}";
@else 
var count =  1;
@endif
function add_more_account_field(row){
    html = ` <tr>
                <td>
                    <select name="credit_account[`+row+`][id]" id="credit_account_`+row+`_id" class="form-control selectpicker">
                        <option value="">Select Please</option>
                        @if (!$transactional_accounts->isEmpty())
                        @foreach ($transactional_accounts as $coa)
                            @if(!empty($coa->customer_id) || !empty($coa->bank_id) || !empty($coa->mobile_bank_id))
                                @if($coa->customer_id)
                                        @if($coa->customer->district_id == $district_id)
                                        <option value="{{ $coa->id }}">{{ $coa->name }} [Customer]</option>
                                        @endif    
                                @elseif($coa->bank_id)
                                    @if($coa->bank->warehouse_id == $warehouse_id)
                                    <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                                    @endif
                                @elseif($coa->mobile_bank_id)
                                    @if($coa->mobile_bank->warehouse_id == $warehouse_id)
                                    <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                                    @endif
                                @endif
                            @else 
                            <option value="{{ $coa->id }}">{{ $coa->name }}</option>
                            @endif
                        @endforeach
                        @endif
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control text-right amount" onkeyup="calculate_total()" name="credit_account[`+row+`][amount]" id="credit_account_`+row+`_amount" placeholder="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove" data-toggle="tooltip" 
                        data-placement="top" data-original-title="Remove">
                        <i class="fas fa-minus-square"></i>
                    </button>
                </td>
            </tr>`;
    $('#debit-voucher-table tbody').append(html);
    $('.selectpicker').selectpicker('refresh');
}

$(document).on('click','#add_more_account',function(){
    count++;
    add_more_account_field(count);
});
$(document).on('click','.remove',function(){
    count--;
    $(this).closest('tr').remove();
    calculate_total();
});

function calculate_total()
{
    var grand_total = 0;
    $('.amount').each(function() {
        if($(this).val() == '' || isNaN($(this).val())){
            grand_total += 0;
        }else{
            grand_total += parseFloat($(this).val());
        }
    });
    $('input[name="grand_total"]').val(parseFloat(grand_total).toFixed(2));
}

function store_data(){
    let form = document.getElementById('credit-voucher-form');
    let formData = new FormData(form);
    let url = "{{url('credit-voucher/update')}}";
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        dataType: "JSON",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function(){
            $('#save-btn').addClass('spinner spinner-white spinner-right');
        },
        complete: function(){
            $('#save-btn').removeClass('spinner spinner-white spinner-right');
        },
        success: function (data) {
            $('#credit-voucher-form').find('.is-invalid').removeClass('is-invalid');
            $('#credit-voucher-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#credit-voucher-form input#' + key).addClass('is-invalid');
                    $('#credit-voucher-form textarea#' + key).addClass('is-invalid');
                    $('#credit-voucher-form select#' + key).parent().addClass('is-invalid');
                    $('#credit-voucher-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('credit-voucher') }}");
                    
                }
            }

        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}


</script>
@endpush