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
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <a href="{{ route('sale.return') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form id="sale_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="customer_id" value="{{ $sale->customer_id }}">

                            <div class="form-group col-md-3 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{ $sale->memo_no }}"  readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="sale_date">Sold Date</label>
                                <input type="text" class="form-control" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="return_date">Return Date</label>
                                <input type="text" class="form-control date" name="return_date" id="return_date" value="{{ date('Y-m-d') }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Order Received By</label>
                                <input type="text" class="form-control" value="{{ $sale->salesmen->name }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Route</label>
                                <input type="text" class="form-control" value="{{ $sale->route->name }}" readonly />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Area</label>
                                <input type="text" class="form-control" value="{{ $sale->area->name }}" readonly />
                            </div>

                            <div class="form-group col-md-3 required">
                                <label for="customer_name">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ $sale->customer->shop_name.' - '.$sale->customer->name }}" readonly />
                            </div>
                            

                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Batch No.</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Sold Qty</th>
                                        <th class="text-center">Return Qty</th>
                                        <th class="text-right">Net Unit Price</th>
                                        <th class="text-right">Deduction (%)</th>
                                        <th class="text-right">Subtotal</th>
                                        <th>Check Return</th>
                                    </thead>
                                    <tbody>
                                        @if (!$sale->sale_products->isEmpty())
                                            @foreach ($sale->sale_products as $key => $sale_product)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$sale_product->pivot->tax_rate)->first();

                                                        $unit_name = DB::table('units')->where('id',$sale_product->pivot->sale_unit_id)->value('unit_name');
                                                        
                                                        $total_return_qty = \DB::table('sale_return_products')
                                                        ->where([
                                                            ['memo_no',$sale->memo_no],
                                                            ['product_id',$sale_product->pivot->product_id],
                                                            ['batch_no', $sale_product->pivot->batch_no]
                                                        ])
                                                        ->sum('return_qty');
                                                        $sold_qty = $sale_product->pivot->qty - $total_return_qty;

      
                                                    @endphp
                                                    <td>{{ $sale_product->name }}</td>
                                                    <td class="text-center">{{ $sale_product->code }}</td>
                                                    <td class="text-center">{{ $sale_product->pivot->batch_no }}</td>
                                                    <td class="unit-name text-center">{{ $unit_name }}</td>
                                                    <td><input type="text" class="sold_qty_{{ $key+1 }} form-control text-center" name="products[{{ $key+1 }}][sold_qty]"  value="{{ $sold_qty }}" readonly></td>
                                                    <td><input type="text" class="form-control return_qty_{{ $key+1 }} text-center" onkeyup="quantity_calculate('{{ $key+1 }}')" onchange="quantity_calculate('{{ $key+1 }}')" name="products[{{ $key+1 }}][return_qty]" id="products_{{ $key+1 }}_return_qty" placeholder="0"></td>
                                                    <td><input type="text" class="net_unit_price_{{ $key+1 }} form-control text-right" name="products[{{ $key+1 }}][net_unit_price]" value="{{ $sale_product->pivot->net_unit_price }}"></td>
                                                    <td><input type="text" class="deduction_rate_{{ $key+1 }} form-control text-right" onkeyup="quantity_calculate('{{ $key+1 }}')" onchange="quantity_calculate('{{ $key+1 }}')" name="products[{{ $key+1 }}][deduction_rate]" placeholder="0.00"></td>
                                                    <td class="sub-total sub-total-{{ $key+1 }} text-right"></td>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="hidden" id="return_{{ $key+1 }}"  name="products[{{ $key+1 }}][return]" value="2">
                                                            <input type="checkbox" class="custom-control-input chk" onchange="setReturnValue('{{ $key+1 }}')"  id="products_{{ $key+1 }}_return_checkbox">
                                                            <label class="custom-control-label" for="products_{{ $key+1 }}_return_checkbox"></label>
                                                        </div>
                                                    </td>
                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]"  value="{{ $sale_product->pivot->product_id }}">
                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]" value="{{ $sale_product->code }}">
                                                    <input type="hidden" class="product-batch" name="products[{{ $key+1 }}][batch_no]" value="{{ $sale_product->pivot->batch_no }}">
                                                    <input type="hidden" class="sale-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="deduction_amount deduction_amount_{{ $key+1 }}" name="products[{{ $key+1 }}][deduction_amount]" >
                                                    <input type="hidden" class="subtotal subtotal_{{ $key+1 }}" name="products[{{ $key+1 }}][total]" >
                                
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" rowspan="3">
                                                <label  for="reason">Reason</label>
                                                <textarea class="form-control" name="reason" id="reason"></textarea><br>
                                            </td>
                                            <td class="text-right" colspan="1"><b>Total Deduction</b></td>
                                            <td class="text-right">
                                                <input type="text" id="total_deduction_ammount" class="form-control text-right" placeholder="0.00" name="total_deduction" readonly="readonly" />
                                            </td>
                                        </tr>
                                        <tr class="d-none">
                                            <td class="text-right" colspan="1" ><b>Total Tax</b></td>
                                            <td class="text-right">
                                                <input type="hidden" name="total_price" id="total_price">
                                                <input type="hidden" name="tax_rate" id="tax_rate" value="{{ $sale->order_tax_rate ? $sale->order_tax_rate : 0 }}">
                                                <input id="total_tax_ammount" tabindex="-1" class="form-control text-right valid" name="total_tax" placeholder="0.00" readonly="readonly" aria-invalid="false" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"  class="text-right"><b>Net Return</b></td>
                                            <td class="text-right">
                                                <input type="text" id="grandTotal" class="form-control text-right" name="grand_total_price" placeholder="0.00" readonly="readonly" />
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="form-grou col-md-12 text-right pt-5">
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="save_data()">Return</button>
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
$(document).ready(function () {
    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    $('#save-btn').prop("disabled", true);
    $('input:checkbox').click(function () {
        if ($(this).is(':checked')) {
            $('#save-btn').prop("disabled", false);
        } else {
            if ($('.chk').filter(':checked').length < 1) {
                $('#save-btn').attr('disabled', true);
            }
        }
    });
});

function setReturnValue(row)
{
    $('#products_'+row+'_return_checkbox').is(':checked') ? $('#return_'+row).val(1) : $('#return_'+row).val(2); 
}

function quantity_calculate(row) {
    var a = 0,o = 0,d = 0,p = 0;
    var sold_qty = $(".sold_qty_" + row).val();
    var return_qty = $(".return_qty_" + row).val();
    var price_item = $(".net_unit_price_" + row).val();
    var deduction_rate = $(".deduction_rate_" + row).val();
    if(parseFloat(sold_qty) < parseFloat(return_qty)){
        alert("Sold quantity less than quantity!");
        $("#return_qty_"+row).val("");
    }
    if (parseFloat(return_qty) > 0) {
        var price = (return_qty * price_item);
        var deduction = price * (deduction_rate / 100);
        $(".deduction_amount_" + row).val(deduction);
        var deduction_amount = $(".deduction_amount_" + row).val();

        //Total price calculate per product
        var temp = price - deduction_amount;
        $(".subtotal_" + row).val(temp);
        $(".sub-total-" + row).text(parseFloat(temp).toFixed(2));

        $(".subtotal").each(function () {
            isNaN(this.value) || o == this.value.length || (a += parseFloat(this.value));
        });
        var tax_rate = parseFloat($('#tax_rate').val());
        var total_tax_ammount = a * (tax_rate/100);
        var grand_total = a + total_tax_ammount;
        $("#total_price").val(a.toFixed(2, 2));
        $("#total_tax_ammount").val(total_tax_ammount.toFixed(2, 2));
        $("#grandTotal").val(grand_total.toFixed(2, 2));

        $(".deduction_amount").each(function () {
            isNaN(this.value) || p == this.value.length || (d += parseFloat(this.value));
        });
        $("#total_deduction_ammount").val(d.toFixed(2, 2));
    }

}

function save_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to return table!")
    }else{
        let form = document.getElementById('sale_update_form');
        let formData = new FormData(form);
        let url = "{{route('sale.return.store')}}";
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
                $('#sale_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#sale_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#sale_update_form input#' + key).addClass('is-invalid');
                        $('#sale_update_form textarea#' + key).addClass('is-invalid');
                        $('#sale_update_form select#' + key).parent().addClass('is-invalid');
                        $('#sale_update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('sale.return') }}");
                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
    
}
</script>
@endpush