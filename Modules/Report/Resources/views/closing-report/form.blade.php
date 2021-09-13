@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
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
                    <form id="closing_balance_form" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-custom card-border">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h3 class="card-label">Closing Account </h3>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="last_day_closing">Last Day Closing</label>
                                            <input type="text" class="form-control bg-secondary" name="last_day_closing" id="last_day_closing" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="cash_in">Collection Receive</label>
                                            <input type="text" class="form-control bg-secondary" name="cash_in" id="cash_in" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="cash_out">Payment</label>
                                            <input type="text" class="form-control bg-secondary" name="cash_out" id="cash_out" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="balance">Balance</label>
                                            <input type="text" class="form-control bg-secondary" name="balance" id="balance" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="transfer">Transfer</label>
                                            <input type="text" class="form-control" name="transfer" id="transfer" onkeyup="cashInHand()">
                                        </div>
                                        <div class="form-group">
                                            <label for="cash_in_hand">Closing Balance</label>
                                            <input type="text" class="form-control bg-primary text-white" name="cash_in_hand" id="cash_in_hand" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-custom card-border">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h3 class="card-label">Cash In  Hand</h3>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-hover">
                                            <thead class="bg-primary">
                                                <tr>
                                                    <th class="text-center">Note Name</th>
                                                    <th class="text-center">Pcs</th>
                                                    <th class="text-center">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="1000">1000</td>
                                                    <td><input type="text" class="form-control text_1 text-right" name="thousands" onkeyup="cashCalculator()"  onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_1_bal text-right" readonly=""></td>
                                                </tr> 
                                                <tr>
                                                    <td class="500">500</td>
                                                    <td><input type="text" class="form-control text_2 text-right" name="fivehnd" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_2_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="100">100</td>
                                                    <td><input type="text" class="form-control text_3 text-right" name="hundrad" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_3_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="50">50</td>
                                                    <td><input type="text" class="form-control text_4 text-right" name="fifty" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_4_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="20">20</td>
                                                    <td><input type="text" class="form-control text_5 text-right" name="twenty" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_5_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="10">10</td>
                                                    <td><input type="text" class="form-control text_6 text-right" name="ten" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_6_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="5">5</td>
                                                    <td><input type="text" class="form-control text_7 text-right" name="five" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_7_bal text-right" readonly=""></td>
                                                </tr>   
                                                <tr>
                                                    <td class="2">2</td>
                                                    <td><input type="text" class="form-control text_8 text-right" name="two" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_8_bal text-right" readonly=""></td>
                                                </tr>
                                                <tr>
                                                    <td class="1">1</td>
                                                    <td><input type="text" class="form-control text_9 text-right" name="one" onkeyup="cashCalculator()" onchange="cashCalculator()"></td>
                                                    <td><input type="text" class="form-control bg-secondary text_9_bal text-right" readonly=""></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-right"><b>Grand Total</b></td>
                                                    <td class=""><input type="text" class="form-control bg-primary text-white total_money text-right" readonly="" name="grndtotal"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                               
                            </div>
                            
                            <div class="form-group col-md-12 text-center pt-5">
                                <button type="button" class="btn btn-danger btn-sm mr-3"><i class="fas fa-sync-alt"></i> Reset</button>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
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
<script>
closing_data();
function closing_data()
{
    $.ajax({
        url: "{{ route('closing.data') }}",
        type: "POST",
        data: {_token:_token},
        dataType: "JSON",
        success: function (data) {
            $('#last_day_closing').val(parseFloat(data.last_day_closing ? data.last_day_closing : 0).toFixed(2));
            $('#cash_in').val(parseFloat(data.cash_in ? data.cash_in : 0).toFixed(2));
            $('#cash_out').val(parseFloat(data.cash_out ? data.cash_out : 0).toFixed(2));
            $('#balance').val(parseFloat(data.cash_in_hand ? data.cash_in_hand : 0).toFixed(2));
            cashInHand();
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}
function cashInHand()
{
    let balance = document.getElementById('balance').value;
    let transfer = document.getElementById('transfer').value;
    if(balance == ''){ balance = 0; }
    if(transfer == ''){ transfer = 0; }
    if(parseFloat(transfer) > parseFloat(balance))
    {
        notification('error','Transfer amount must be less than or equal to balance amount!');
        document.getElementById('transfer').value = parseFloat(balance).toFixed(2);
        cashInHand();
    }else{
        let cash_in_hand = parseFloat(balance) - parseFloat(transfer);
        document.getElementById('cash_in_hand').value = parseFloat(cash_in_hand).toFixed(2);
    }
    
}
function store_data(){
    let form = document.getElementById('closing_balance_form');
    let formData = new FormData(form);
    let url = "{{route('closing.store')}}";
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
            $('#closing_balance_form').find('.is-invalid').removeClass('is-invalid');
            $('#closing_balance_form').find('.error').remove();
            if (data.status == false) {
                $.each(data.errors, function (key, value) {
                    var key = key.split('.').join('_');
                    $('#closing_balance_form input#' + key).addClass('is-invalid');
                    $('#closing_balance_form textarea#' + key).addClass('is-invalid');
                    $('#closing_balance_form select#' + key).parent().addClass('is-invalid');
                    $('#closing_balance_form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                });
            } else {
                notification(data.status, data.message);
                if (data.status == 'success') {
                    window.location.replace("{{ route('closing.report') }}");
                    
                }
            }

        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}

function cashCalculator() {

    var mul1 = $('.text_1').val();
    var text_1_bal = mul1 * 1000;
    $('.text_1_bal').val(text_1_bal);

    var mul2 = $('.text_2').val();
    var text_2_bal = mul2 * 500;
    $('.text_2_bal').val(text_2_bal);

    var mul3 = $('.text_3').val();
    var text_3_bal = mul3 * 100;
    $('.text_3_bal').val(text_3_bal);

    var mul4 = $('.text_4').val();
    var text_4_bal = mul4 * 50;
    $('.text_4_bal').val(text_4_bal);

    var mul5 = $('.text_5').val();
    var text_5_bal = mul5 * 20;
    $('.text_5_bal').val(text_5_bal);

    var mul6 = $('.text_6').val();
    var text_6_bal = mul6 * 10;
    $('.text_6_bal').val(text_6_bal);

    var mul7 = $('.text_7').val();
    var text_7_bal = mul7 * 5;
    $('.text_7_bal').val(text_7_bal);

    var mul8 = $('.text_8').val();
    var text_8_bal = mul8 * 2;
    $('.text_8_bal').val(text_8_bal);

    var mul9 = $('.text_9').val();
    var text_9_bal = mul9 * 1;
    $('.text_9_bal').val(text_9_bal);


    var total_money = (text_1_bal + text_2_bal + text_3_bal + text_4_bal + text_5_bal + text_6_bal + text_7_bal + text_8_bal + text_9_bal);

    $('.total_money').val(total_money);
}
</script>
@endpush