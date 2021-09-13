@extends('layouts.app')

@section('title', $page_title)


@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
    
                    <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i> Print</button>
                   <a href="{{ route('transfer') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body" style="padding-bottom: 100px !important;">
                <div class="col-md-12 col-lg-12"  style="width: 100%;">
                    <div id="invoice">
                        <style>
                            body,html {
                                background: #fff !important;
                                -webkit-print-color-adjust: exact !important;
                            }

                            .invoice {
                                /* position: relative; */
                                background: #fff !important;
                                /* min-height: 680px; */
                            }

                            .invoice header {
                                padding: 10px 0;
                                margin-bottom: 20px;
                                border-bottom: 1px solid #036;
                            }

                            .invoice .company-details {
                                text-align: right
                            }

                            .invoice .company-details .name {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .invoice .contacts {
                                margin-bottom: 20px;
                            }

                            .invoice .invoice-to {
                                text-align: left;
                            }

                            .invoice .invoice-to .to {
                                margin-top: 0;
                                margin-bottom: 0;
                            }

                            .invoice .invoice-details {
                                text-align: right;
                            }

                            .invoice .invoice-details .invoice-id {
                                margin-top: 0;
                                color: #036;
                            }

                            .invoice main {
                                padding-bottom: 50px
                            }

                            .invoice main .thanks {
                                margin-top: -100px;
                                font-size: 2em;
                                margin-bottom: 50px;
                            }

                            .invoice main .notices {
                                padding-left: 6px;
                                border-left: 6px solid #036;
                            }

                            .invoice table {
                                width: 100%;
                                border-collapse: collapse;
                                border-spacing: 0;
                                margin-bottom: 20px;
                            }

                            .invoice table th {
                                background: #036;
                                color: #fff;
                                padding: 10px;
                                border-bottom: 1px solid #fff
                            }

                            .invoice table td {
                                padding: 10px;
                                border-bottom: 1px solid #fff
                            }
                            #info-table td{
                                padding: 2px !important;
                            }

                            .invoice table th {
                                white-space: nowrap;
                            }

                            .invoice table td h3 {
                                margin: 0;
                                color: #036;
                            }

                            .invoice table .qty {
                                text-align: center;
                            }

                            .invoice table .price,
                            .invoice table .discount,
                            .invoice table .tax,
                            .invoice table .total {
                                text-align: right;
                            }

                            .invoice table .no {
                                color: #fff;
                                background: #036
                            }

                            .invoice table .total {
                                background: #036;
                                color: #fff
                            }

                            .invoice table tbody tr:last-child td {
                                border: none
                            }

                            .invoice table tfoot td {
                                background: 0 0;
                                border-bottom: none;
                                white-space: nowrap;
                                text-align: right;
                                padding: 10px 20px;
                                border-top: 1px solid #aaa;
                                font-weight: bold;
                            }

                            .invoice table tfoot tr:first-child td {
                                border-top: none
                            }

                            /* .invoice table tfoot tr:last-child td {
                                color: #036;
                                border-top: 1px solid #036
                            } */

                            .invoice table tfoot tr td:first-child {
                                border: none
                            }

                            .invoice footer {
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .invoice a {
                                content: none !important;
                                text-decoration: none !important;
                                color: #036 !important;
                            }

                            .page-header,
                            .page-header-space {
                                height: 100px;
                            }

                            .page-footer,
                            .page-footer-space {
                                height: 20px;

                            }

                            .page-footer {
                                position: fixed;
                                bottom: 0;
                                width: 100%;
                                text-align: center;
                                color: #777;
                                border-top: 1px solid #aaa;
                                padding: 8px 0
                            }

                            .page-header {
                                position: fixed;
                                top: 0mm;
                                width: 100%;
                                border-bottom: 1px solid black;
                            }

                            .page {
                                page-break-after: always;
                            }
                            .dashed-border{
                                width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                            }

                            @media screen {
                                .no_screen {display: none;}
                                .no_print {display: block;}
                                thead {display: table-header-group;} 
                                tfoot {display: table-footer-group;}
                                button {display: none;}
                                body {margin: 0;}
                            }

                            @media print {

                                body,
                                html {
                                    /* background: #fff !important; */
                                    -webkit-print-color-adjust: exact !important;
                                    font-family: sans-serif;
                                    /* font-size: 12px !important; */
                                    margin-bottom: 100px !important;
                                }

                                .m-0 {
                                    margin: 0 !important;
                                }

                                h1,
                                h2,
                                h3,
                                h4,
                                h5,
                                h6 {
                                    margin: 0 !important;
                                }

                                .no_screen {
                                    display: block !important;
                                }

                                .no_print {
                                    display: none;
                                }

                                a {
                                    content: none !important;
                                    text-decoration: none !important;
                                    color: #036 !important;
                                }

                                .text-center {
                                    text-align: center !important;
                                }

                                .text-left {
                                    text-align: left !important;
                                }

                                .text-right {
                                    text-align: right !important;
                                }

                                .float-left {
                                    float: left !important;
                                }

                                .float-right {
                                    float: right !important;
                                }

                                .text-bold {
                                    font-weight: bold !important;
                                }

                                .invoice {
                                    /* font-size: 11px!important; */
                                    overflow: hidden !important;
                                    background: #fff !important;
                                    margin-bottom: 100px !important;
                                }

                                .invoice footer {
                                    position: absolute;
                                    bottom: 0;
                                    left: 0;
                                    /* page-break-after: always */
                                }

                                /* .invoice>div:last-child {
                                    page-break-before: always
                                } */
                                .hidden-print {
                                    display: none !important;
                                }
                                .dashed-border{
                                    width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                }
                                #info-table td{
                                    padding: 2px !important;
                                }
                            }

                            @page {
                                /* size: auto; */
                                margin: 5mm 5mm;

                            }
                        </style>
                        <div class="invoice overflow-auto">
                            <div>
                                <table>
                                    <tr>
                                        <td class="text-center">
                                            <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                            @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>Contact No.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>Email: </b>{{ config('settings.email') }}@endif</p>@endif
                                            @if(config('settings.address'))<p style="font-weight: normal;margin:0;">{{ config('settings.address') }}</p>@endif
                                            <p style="font-weight: normal;margin:0;"><b>Date: </b>{{ date('d-M-Y') }}</p>
                                        </td>
                                    </tr>
                                </table>
                                <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                                <table id="info-table" style="margin-top: 10px;">
                                    <tr><td width="20%"><b>Chalan No.</b></td><td width="5%"><b>:</b></td><td><b>{{ $transfer->chalan_no }}</b></td></tr>
                                    <tr><td width="20%"><b>Batch No.</b></td><td width="5%"><b>:</b></td><td>{{ $transfer->production->batch_no }}</td></tr>
                                    <tr><td width="20%"><b>Warehouse</b></td><td width="5%"><b>:</b></td><td>{{ $transfer->warehouse->name }}</td></tr>
                                    <tr><td width="20%"><b>Transfer Date</b></td><td width="5%"><b>:</b></td><td>{{ date('d-M-Y',strtotime($transfer->transfer_date)) }}</td></tr>
                                </table>

                                <table border="0" cellspacing="0" cellpadding="0">
                                    <thead>
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Base Unit</th>
                                        <th class="text-center">Qty Unit</th>
                                        <th class="text-center">Qty Base Unit</th>
                                        <th class="text-right">Net Unit Cost</th>
                                        <th class="text-right">Tax</th>
                                        <th class="text-right">Sub Total</th>
                                    </thead>
                                    <tbody>
                                    @if (!$transfer->products->isEmpty())
                                        @foreach ($transfer->products as $key => $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-center">{{ $item->code }}</td>
                                            <td class="text-center">{{ $item->unit->unit_name.' ('.$item->unit->unit_code.')' }}</td>
                                            <td class="text-center">{{ $item->base_unit->unit_name.' ('.$item->base_unit->unit_code.')' }}</td>
                                            <td class="text-center">{{ number_format($item->pivot->unit_qty,2,'.','') }}</td>
                                            <td class="text-center">{{ number_format($item->pivot->base_unit_qty,2,'.','') }}</td>
                                            <td class="text-right">{{ number_format($item->pivot->net_unit_price,2,'.','') }}</td>
                                            <td class="text-right">{{ number_format(($item->pivot->tax),2,'.','') }}</td>
                                            <td class="text-right">{{ number_format(($item->pivot->total),2,'.','') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6"></td>
                                            <td colspan="2"  class="text-right">TOTAL</td>
                                            <td class="text-right">
                                                @if (config('settings.currency_position') == 2)
                                                    {{ number_format($transfer->total,2) }} {{ config('settings.currency_symbol') }}
                                                @else 
                                                    {{ config('settings.currency_symbol') }} {{ number_format($transfer->total,2) }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"></td>
                                            <td colspan="2"  class="text-right">SHIPPING COST</td>
                                            <td class="text-right">
                                                @if (config('settings.currency_position') == 2)
                                                    {{ number_format($transfer->shipping_cost,2) }} {{ config('settings.currency_symbol') }}
                                                @else 
                                                    {{ config('settings.currency_symbol') }} {{ number_format($transfer->shipping_cost,2) }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"></td>
                                            <td colspan="2"  class="text-right">LABOR COST</td>
                                            <td class="text-right">
                                                @if (config('settings.currency_position') == 2)
                                                    {{ number_format($transfer->labor_cost,2) }} {{ config('settings.currency_symbol') }}
                                                @else 
                                                    {{ config('settings.currency_symbol') }} {{ number_format($transfer->shipping_cost,2) }}
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="6"></td>
                                            <td colspan="2"  class="text-right">GRAND TOTAL</td>
                                            <td class="text-right">
                                                @if (config('settings.currency_position') == 2)
                                                    {{ number_format($transfer->grand_total,2) }} {{ config('settings.currency_symbol') }}
                                                @else 
                                                    {{ config('settings.currency_symbol') }} {{ number_format($transfer->grand_total,2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <table style="width: 100%;">
                                    <tr>
                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:left;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $transfer->carried_by }}</b>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Carried By</p>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <div class="font-size-10" style="width:250px;float:right;">
                                                <p style="margin:0;padding:0;"><b class="text-uppercase">{{ $transfer->received_by }}</b></p>
                                                <p class="dashed-border"></p>
                                                <p style="margin:0;padding:0;">Received By</p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    //QR Code Print
    $(document).on('click','#print-invoice',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#invoice").printArea(options);
    });
});

</script>
@endpush