@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    #dataTable{
        width: 3000px !important;
    }
    #dataTable ul li{
        border-bottom: 1px solid #EBEDF3;
        margin-bottom: 5px;
    }
    #dataTable ul li:last-child{
        border-bottom: 0;
        margin-bottom: 0;
    }

    #dataTable tbody tr td:nth-child(8),
    #dataTable tbody tr td:nth-child(9),
    #dataTable tbody tr td:nth-child(10),
    #dataTable tbody tr td:nth-child(11),
    #dataTable tbody tr td:nth-child(12),
    #dataTable tbody tr td:nth-child(13),
    #dataTable tbody tr td:nth-child(14),
    #dataTable tbody tr td:nth-child(15)
    {
        padding-left: 0px !important;
        padding-right: 0px !important;
    }
</style>
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
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.textbox labelName="Memo No." name="memo_no" col="col-md-3" />
                        <div class="form-group col-md-3">
                            <label for="name">Choose Your Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control daterangepicker-filed" value="{{ date('Y-m-d') }} To {{ date('Y-m-d') }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ date('Y-m-d')}}">
                            </div>
                        </div>
                        <x-form.selectbox labelName="Order Received By" name="salesmen_id" col="col-md-3" class="selectpicker">
                            @if (!$salesmen->isEmpty())
                                @foreach ($salesmen as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Upazila" name="upazila_id" col="col-md-3" class="selectpicker" onchange="getRouteList(this.value)">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 2 && $location->parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Route" name="route_id" col="col-md-3" class="selectpicker" onchange="getAreaList(this.value);">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 3 && $location->grand_parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>

                        <x-form.selectbox labelName="Area" name="area_id" col="col-md-3" class="selectpicker" onchange="customer_list(this.value)">
                            @if (!$locations->isEmpty())
                                @foreach ($locations as $location)
                                    @if ($location->type == 4 && $location->grand_grand_parent_id == auth()->user()->district_id)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </x-form.selectbox>
                        <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker"/>

                        <div class="col-md-3">
                            <div style="margin-top:28px;">     
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>
    
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Sale Date</th>
                                        <th>Sale By</th>
                                        <th>Memo No.</th>
                                        <th>Route</th>
                                        <th>Area</th>
                                        <th>Customer Name</th>

                                        <th>Product Description</th>
                                        <th>Code</th>
                                        <th>Batch No</th>
                                        <th>Sale Unit</th>
                                        <th>Quantity</th>
                                        <th>Net Sale Unit Price</th>
                                        <th>Tax</th>
                                        <th>Subtotal</th>

                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Tax Rate(%)</th>
                                        <th>Order Tax</th>
                                        <th>Discount</th>
                                        <th>Labor Cost</th>
                                        <th>Shipping Cost</th>
                                        
                                        <th>Grand Total</th>
                                        <th>Previous Due</th>
                                        <th>Net Total</th>
                                        <th>Paid Amount</th>
                                        <th>Due Amount</th>

                                        <th>Payment Method</th>
                                        <th>Account</th>
                                        <th>Delivery Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="bg-primary">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;"></th>
                                        <th style="text-align: right !important;font-weight:bold;#F64E60;color:white;" id="total_due"></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
<script src="js/moment.js"></script>
<script src="js/knockout-3.4.2.js"></script>
<script src="js/daterangepicker.min.js"></script>
<script>
$('.daterangepicker-filed').daterangepicker({
    callback: function(startDate, endDate, period){
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date   = endDate.format('YYYY-MM-DD');
        var title = start_date + ' To ' + end_date;
        $(this).val(title);
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);
    }
});
var table;
$(document).ready(function(){
    table = $('#dataTable').DataTable({
        "processing": true, //Feature control the processing indicator
        "serverSide": true, //Feature control DataTable server side processing mode
        "order": [], //Initial no order
        "responsive": false, //Make table responsive in mobile device
        "bInfo": true, //TO show the total number of data
        "bFilter": false, //For datatable default search box show/hide
        "lengthMenu": [
            [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
            [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
        ],
        "pageLength": 25, //number of data show per page
        "language": { 
            processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
            emptyTable: '<strong class="text-danger">No Data Found</strong>',
            infoEmpty: '',
            zeroRecords: '<strong class="text-danger">No Data Found</strong>'
        },
        "ajax": {
            "url": "{{route('sales.report.datatable.data')}}",
            "type": "POST",
            "data": function (data) {
                data.memo_no        = $("#form-filter #memo_no").val();
                data.start_date     = $("#form-filter #start_date").val();
                data.end_date       = $("#form-filter #end_date").val();
                data.salesmen_id    = $("#form-filter #salesmen_id").val();
                data.customer_id    = $("#form-filter #customer_id").val();
                data.upazila_id     = $("#form-filter #upazila_id").val();
                data.route_id       = $("#form-filter #route_id").val();
                data.area_id        = $("#form-filter #area_id").val();
                data.payment_status = $("#form-filter #payment_status").val();
                data._token         = _token;
            }
        },
        "columnDefs": [
            {
                "targets": [7,8,9,10,11,12,13,14],
                "orderable": false,
            },
            {
                "targets": [0,1,2,3,4,5,6,8,9,10,11,15,27,28,29],
                "className": "text-center"
            },
            {
                "targets": [12,13,14,16,17,18,19,20,21,22,23,24,25,26],
                "className": "text-right"
            },

        ],
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

        "buttons": [
            {
                'extend':'colvis','className':'btn btn-secondary btn-sm text-white','text':'Column','columns': ':gt(0)'
            },
            {
                "extend": 'print',
                'text':'Print',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "orientation": "landscape", //portrait
                "pageSize": "legal", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                            return table.column(index).visible();
                        }
                },
                customize: function (win) {
                    $(win.document.body).addClass('bg-white');
                    $(win.document.body).find('table thead').css({'background':'#034d97'});
                    $(win.document.body).find('table tfoot tr').css({'background-color':'#034d97'});
                    $(win.document.body).find('h1').css('text-align', 'center');
                    $(win.document.body).find('h1').css('font-size', '15px');
                    $(win.document.body).find('table').css( 'font-size', 'inherit' );
                },
                footer:true,
            },
            {
                "extend": 'csv',
                'text':'CSV',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    columns: function (index, data, node) {
                            return table.column(index).visible();
                        }
                },
                footer:true,
            },
            {
                "extend": 'excel',
                'text':'Excel',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "exportOptions": {
                    columns: function (index, data, node) {
                            return table.column(index).visible();
                        } 
                },
                footer:true,
            },
            {
                "extend": 'pdf',
                'text':'PDF',
                'className':'btn btn-secondary btn-sm text-white',
                "title": "{{ $page_title }} List",
                "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                "orientation": "landscape", //portrait
                "pageSize": "legal", //A3,A5,A6,legal,letter
                "exportOptions": {
                    columns: function (index, data, node) {
                            return table.column(index).visible();
                        }
                },
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10 
                    doc.styles.tableHeader.fontSize = 7;
                    doc.styles.tableFooter.fontSize = 7;
                    doc.pageMargins = [5,5,5,5];
                }  ,
                footer:true,
            },
        ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // Remove the formatting to get integer data for summation
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            const jsonData = table.ajax.json();
            $(api.column(27).footer()).html('Total Due Amount = '+number_format(jsonData.total_due)+' Tk');
            $(api.column(15).footer()).html(jsonData.total_items);
            const column_index = [16,18,19,20,21,22,25,26];
            for (let index = 16; index <= 26; index++) {
                    // Total over this page
                if(column_index.includes(index))
                {
                    total = api
                    .column( index )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                    // Total over this page
                    pageTotal = api
                        .column(index, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                     // Update footer

                     let column_text = '';
                     switch (index) {
                        case 16:
                            column_text = 'On Date Total ';
                            break;
                        case 18:
                            column_text = 'On Date Total Tax ';
                            break;
                        case 19:
                            column_text = 'On Date Total Discount ';
                            break;
                        case 20:
                            column_text = 'On Date Total Labor Cost ';
                            break;
                        case 21:
                            column_text = 'On Date Total Shipping Cost';
                            break;
                        case 22:
                            column_text = 'On Date Grand Total ';
                            break;
                        case 25:
                            column_text = 'On Date Total Paid ';
                            break;
                        case 26:
                            column_text = 'On Date Due Amount ';
                            break;
                    }

                    $( api.column( index ).footer() ).html(column_text+'= '+number_format(total));
                }
    
               
            }
        }
    });

    $('#btn-filter').click(function () {
        table.ajax.reload();
    });

    $('#btn-reset').click(function () {
        $('#form-filter')[0].reset();
        $('#form-filter .selectpicker').selectpicker('refresh');
        $('#form-filter #start_date').val("{{ date('Y-m-d') }}");
        $('#form-filter #end_date').val("{{ date('Y-m-d') }}");
        table.ajax.reload();
    });


    $(document).on('click', '.delete_data', function () {
        let id    = $(this).data('id');
        let name  = $(this).data('name');
        let row   = table.row($(this).parent('tr'));
        let url   = "{{ route('sale.delete') }}";
        delete_data(id, url, table, row, name);
    });

});
</script>
@endpush