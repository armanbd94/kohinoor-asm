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
                    <form id="contra-voucher-form" method="post">
                        @csrf
                        <div class="row">
                                <div class="form-group col-md-4 required">
                                    <label for="voucher_no">Voucher No</label>
                                    <input type="text" class="form-control" name="voucher_no" id="voucher_no" value="{{ $voucher[0]->voucher_no }}" readonly />
                                </div>
                                <div class="form-group col-md-4 required">
                                    <label for="voucher_date">Date</label>
                                    <input type="text" class="form-control date" name="voucher_date" id="voucher_date" value="{{ $voucher[0]->voucher_date }}" readonly />
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="debit-voucher-table">
                                        <thead class="bg-primary">
                                            <th width="40%">Account Name</th>
                                            <th width="25%" class="text-right">Debit</th>
                                            <th width="25%" class="text-right">Credit</th>
                                            <th width="10%"></th>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total_debit = 0;
                                                $total_credit = 0;
                                            @endphp     
                                            @if (!$voucher->isEmpty())
                                                @foreach ($voucher as $key => $contrav)
                                                @php
                                                    $total_debit += $contrav->debit;
                                                    $total_credit += $contrav->credit;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <select name="contra_account[{{ $key + 1 }}][id]" id="contra_account_{{ $key + 1 }}_id" class="form-control selectpicker">
                                                            <option value="">Select Please</option>
                                                            @if (!$transactional_accounts->isEmpty())
                                                            @foreach ($transactional_accounts as $coa)
                                                                @if(!empty($coa->customer_id) || !empty($coa->bank_id) || !empty($coa->mobile_bank_id))
                                                                    @if($coa->customer_id)
                                                                            @if($coa->customer->district_id == $district_id)
                                                                            <option value="{{ $coa->id }}" {{ ($contrav->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }} [Customer]</option>
                                                                            @endif    
                                                                    @elseif($coa->bank_id)
                                                                        @if($coa->bank->warehouse_id == $warehouse_id)
                                                                        <option value="{{ $coa->id }}" {{ ($contrav->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                        @endif
                                                                    @elseif($coa->mobile_bank_id)
                                                                        @if($coa->mobile_bank->warehouse_id == $warehouse_id)
                                                                        <option value="{{ $coa->id }}" {{ ($contrav->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                        @endif
                                                                    @endif
                                                                @else 
                                                                <option value="{{ $coa->id }}" {{ ($contrav->chart_of_account_id == $coa->id) ? 'selected' : '' }}>{{ $coa->name }}</option>
                                                                @endif
                                                                
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right debit_amount" value="{{ $contrav->debit }}" onkeyup="calculate_total(1)" name="contra_account[{{ $key + 1 }}][debit_amount]" id="contra_account_{{ $key + 1 }}_debit_amount" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control text-right credit_amount" value="{{ $contrav->credit }}" onkeyup="calculate_total(2)" name="contra_account[{{ $key + 1 }}][credit_amount]" id="contra_account_{{ $key + 1 }}_credit_amount" placeholder="0.00">
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
                                                <td><input type="text" class="form-control text-right bg-primary text-white" value="{{ $total_debit }}" name="debit_grand_total" id="debit_grand_total" placeholder="0.00" readonly></td>
                                                <td><input type="text" class="form-control text-right bg-primary text-white" value="{{ $total_credit }}" name="credit_grand_total" id="credit_grand_total" placeholder="0.00" readonly></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-primary btn-sm" id="add_more_account"
                                                    data-toggle="tooltip" data-placement="top" data-original-title="Add More"><i class="fas fa-plus-square"></i></button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <x-form.textarea labelName="Remarks" name="remarks" col="col-md-12" value="{{ $voucher[0]->description }}"/>
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
@if(!$voucher->isEmpty())
var count = "{{ count($voucher) }}";
@else 
var count =  1;
@endif
function add_more_account_field(row){
    html = ` <tr>
                <td>
                    <select name="contra_account[`+row+`][id]" id="contra_account_`+row+`_id" class="form-control selectpicker">
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
                    <input type="text" class="form-control text-right debit_amount" onkeyup="calculate_total(1)" name="contra_account[`+row+`][debit_amount]" id="contra_account_`+row+`_debit_amount" placeholder="0.00">
                </td>
                <td>
                    <input type="text" class="form-control text-right credit_amount" onkeyup="calculate_total(2)" name="contra_account[`+row+`][credit_amount]" id="contra_account_`+row+`_credit_amount" placeholder="0.00">
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

function calculate_total(type)
{
    if(type == 1)
    {
        var debit_grand_total = 0;
        $('.debit_amount').each(function() {
            if($(this).val() == '' || isNaN($(this).val())){
                debit_grand_total += 0;
            }else{
                debit_grand_total += parseFloat($(this).val());
            }
        });
        $('input[name="debit_grand_total"]').val(parseFloat(debit_grand_total).toFixed(2));
    }else{
        var credit_grand_total = 0;
        $('.credit_amount').each(function() {
            if($(this).val() == '' || isNaN($(this).val())){
                credit_grand_total += 0;
            }else{
                credit_grand_total += parseFloat($(this).val());
            }
        });
        $('input[name="credit_grand_total"]').val(parseFloat(credit_grand_total).toFixed(2));
    }
}

function store_data(){
    let form = document.getElementById('contra-voucher-form');
    let formData = new FormData(form);
    let url = "{{url('contra-voucher/update')}}";
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
            $('#contra-voucher-form').find('.is-invalid').removeClass('is-invalid');
            $('#contra-voucher-form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#contra-voucher-form input#' + key).addClass('is-invalid');
                    $('#contra-voucher-form textarea#' + key).addClass('is-invalid');
                    $('#contra-voucher-form select#' + key).parent().addClass('is-invalid');
                    $('#contra-voucher-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ url('contra-voucher') }}");
                    
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