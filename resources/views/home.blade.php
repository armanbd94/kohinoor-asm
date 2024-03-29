@extends('layouts.app')

@section('title','Dashboard')

@push('styles')
<link rel="stylesheet" href="css/chart.min.css">
<style>
    .today-btn{
        border-radius: 5px 0 0 5px !important;
    }
    .week-btn,.month-btn{
        border-radius: 0 !important;
    }
    .year-btn{
        border-radius: 0 5px 5px 0 !important;
    }
    .icon{
        width: 40px;
        height: 40px;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/transfer.svg" alt="transfer" class="icon">
                    </span>
                    <h6 id="transfer" class="m-0">{{ number_format(0,2) }}TK</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Transfer</a>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/sale.svg" alt="sale" class="icon">
                    </span>
                    <h6 id="sale" class="m-0">{{ number_format(0,2) }}TK</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Sale</a>
                </div>
            </div>

            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/income.svg" alt="income" class="icon">
                    </span>
                    <h6 id="income" class="m-0">{{ number_format(0,2) }}Tk</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Income</a>
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/expense.svg" alt="expense" class="icon">
                    </span>
                    <h6 id="expense" class="m-0">{{ number_format(0,2) }}TK</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Expense</a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/coupon.svg" alt="coupon" class="icon">
                    </span>
                    <h6 id="coupon" class="m-0">0 Piece</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Coupon Received</a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bg-white text-center py-3  rounded-xl">
                    <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-3">
                        <img src="images/payment.svg" alt="payment" class="icon">
                    </span>
                    <h6 id="payment" class="m-0">{{ number_format(0,2) }}TK</h6>
                    <a href="javascript::void(0);" class="font-weight-bold font-size-h7 mt-2">Coupon Payment</a>
                </div>
            </div>
        </div>
        <!-- Start :: Bar Chart-->
        <div class="row py-5">
            <div class="col-md-12">
            <div class="card bar-chart">
                <div class="card-header d-flex align-items-center">
                <h4>Yearly Report </h4>
                </div>
                <div class="card-body">
                    <canvas id="yearlyReportChart"  data-sale_chart_value="{{ json_encode( $yearly_sale_amount) }}"
                    data-transfer_chart_value="{{ json_encode($yearly_transfer_amount) }}"  data-label1="Transfer Amount" data-label2="Sale Amount"></canvas>
                </div>
            </div>
            </div>
        </div>
        <!-- End :: Bar Chart-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/chart.min.js"></script>
<script>
$(document).ready(function(){
    loadData("{{ date('Y-m-d') }}","{{ date('Y-m-d') }}");
    $('.data-btn').on('click',function(){
        $('.data-btn').removeClass('active');
        $(this).addClass('active');
        var start_date = $(this).data('start_date');
        var end_date = $(this).data('end_date');
        loadData(start_date,end_date);
    });
    
    function loadData(start_date,end_date)
    {
        $.get("{{ url('dashboard-data') }}/"+start_date+'/'+end_date, function(data){
            $('#sale').text((data.sale).toFixed(2)+'Tk');
            $('#transfer').text((data.transfer).toFixed(2)+'Tk');
            $('#income').text((data.income).toFixed(2)+'Tk');
            $('#expense').text((data.expense).toFixed(2)+'Tk');
            $('#payment').text((data.total_coupon_payment).toFixed(2)+'Tk');
            if(data.total_coupon_received == 0 || data.total_coupon_received == 1)
            {
                $('#coupon').text(data.total_coupon_received+' Piece');
            }else{
                $('#coupon').text(data.total_coupon_received+' Pieces');
            }
            
        });
    }

    //Yearly Report Chart

    var YEARLYREPORTCHART = $('#yearlyReportChart');
    if(YEARLYREPORTCHART.length > 0)
    {
        var yearly_sale_amount = YEARLYREPORTCHART.data('sale_chart_value');
        var yearly_transfer_amount = YEARLYREPORTCHART.data('transfer_chart_value');
        var label1 = YEARLYREPORTCHART.data('label1');
        var label2 = YEARLYREPORTCHART.data('label2');

        var yearly_report_chart = new Chart(YEARLYREPORTCHART, {
            type:'bar',
            data:{
            labels:["January","February","March","April","May","June","July","August","September","October","November","December"],
            datasets:[
                {
                label:label1,
                backgroundColor:[
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                    'rgba(245, 34, 45, 1)',
                ],
                borderColor:[
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                    '#f5222d',
                ],
                borderWidth:1,
                data:[
                    yearly_transfer_amount[0],yearly_transfer_amount[1],yearly_transfer_amount[2],yearly_transfer_amount[3],
                    yearly_transfer_amount[4],yearly_transfer_amount[5],yearly_transfer_amount[6],yearly_transfer_amount[7],
                    yearly_transfer_amount[8],yearly_transfer_amount[9],yearly_transfer_amount[10],yearly_transfer_amount[11], 0
                    ],
                },
                {
                label:label2,
                backgroundColor:[
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                    'rgba(3, 77, 151, 1)',
                ],
                borderColor:[
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                    '#034d97',
                ],
                borderWidth:1,
                data:[
                    yearly_sale_amount[0],yearly_sale_amount[1],yearly_sale_amount[2],yearly_sale_amount[3],
                    yearly_sale_amount[4],yearly_sale_amount[5],yearly_sale_amount[6],yearly_sale_amount[7],
                    yearly_sale_amount[8],yearly_sale_amount[9],yearly_sale_amount[10],yearly_sale_amount[11], 0
                    ],
                },
            ]
            }
        });
    }
});
</script>
@endpush
