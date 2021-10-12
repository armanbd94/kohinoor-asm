@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
<style>
    .customer.table td{
        vertical-align: top !important;
        padding: 0 !important;
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
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <a href="{{ route('sale') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
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
                    <form action="" id="sale_update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="sale_id" id="sale_id" value="{{ $sale->id }}">
                            <div class="form-group col-md-3 required">
                                <label for="memo_no">Memo No.</label>
                                <input type="text" class="form-control" name="memo_no" id="memo_no" value="{{  $sale->memo_no }}"  />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="sale_date">Sale Date</label>
                                <input type="text" class="form-control date" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly />
                            </div>

                            <div class="form-group col-md-3 required">
                                <label>Order Received By.</label>
                                <input type="text" class="form-control" value="{{  $sale->salesmen->name }}" readonly  />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Route</label>
                                <input type="text" class="form-control" value="{{  $sale->route->name }}" readonly  />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Area</label>
                                <input type="text" class="form-control" value="{{  $sale->area->name }}" readonly  />
                            </div>
                            <div class="form-group col-md-3 required">
                                <label>Customer</label>
                                <input type="text" class="form-control" value="{{  $sale->customer->name.' - '.$sale->customer->shop_name }}" readonly  />
                                <input type="hidden" name="customer_id_hidden" id="customer_id_hidden" value="{{ $sale->customer_id }}">
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label for="document">Attach Document</label>
                                <input type="file" class="form-control" name="document" id="document">
                            </div>

                            <div class="form-group col-md-12">
                                <label for="product_code_name">Select Product</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Please type product code and select...">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Batch No.</th>
                                        <th class="text-center">Sale Unit</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Net Sale Unit Price</th>
                                        <th class="text-right">Tax</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center"><i class="fas fa-trash text-white"></i></th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $temp_unit_name = [];
                                            $temp_unit_operator = [];
                                            $temp_unit_operation_value = [];
                                        @endphp
                                        @if (!$sale->sale_products->isEmpty())
                                            @foreach ($sale->sale_products as $key => $sale_product)
                                            <tr>
                                                @php
                                                    $tax = DB::table('taxes')->where('rate',$sale_product->pivot->tax_rate)->first();

                                                    $units = DB::table('units')->where('base_unit',$sale_product->pivot->sale_unit_id)
                                                                                ->orWhere('id',$sale_product->pivot->sale_unit_id)
                                                                                ->get();
                                                    $warehouse_product = DB::table('warehouse_product')->where([
                                                                            ['batch_no',$sale_product->pivot->batch_no],
                                                                            ['warehouse_id', $sale->warehouse_id],
                                                                            ['product_id',$sale_product->pivot->product_id]
                                                                        ])->first();
                                                    $stock_qty = $sale_product->pivot->qty + ($warehouse_product ? $warehouse_product->qty : 0);
                                                    
                                                    $unit_name            = [];
                                                    $unit_operator        = [];
                                                    $unit_operation_value = [];

                                                    if($units){
                                                        foreach ($units as $unit) {
                                                            if($sale_product->pivot->sale_unit_id == $unit->id)
                                                            {
                                                                array_unshift($unit_name,$unit->unit_name);
                                                                array_unshift($unit_operator,$unit->operator);
                                                                array_unshift($unit_operation_value,$unit->operation_value);
                                                            }else{
                                                                $unit_name           [] = $unit->unit_name;
                                                                $unit_operator       [] = $unit->operator;
                                                                $unit_operation_value[] = $unit->operation_value;
                                                            }
                                                        }

                                                        if($sale_product->tax_method == 1){
                                                            $product_price = $sale_product->pivot->net_unit_price;
                                                        }else{
                                                            $product_price = $sale_product->pivot->total / $sale_product->pivot->qty;
                                                        }

                                                        if($unit_operator[0] == '*')
                                                        {
                                                            $product_price = $product_price * $unit_operation_value[0];
                                                        }else if($unit_operator[0] == '/')
                                                        {
                                                            $product_price = $product_price / $unit_operation_value[0];
                                                        }
                                                        
                                                        $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                        $temp_unit_operator = $unit_operator = implode(",",$unit_operator).',';
                                                        $temp_unit_operation_value = $unit_operation_value = implode(",",$unit_operation_value).',';
                                                    }
                                                @endphp
                                                <td>{{ $sale_product->name }}</td>
                                                <td class="text-center">{{ $sale_product->code }}</td>
                                                <td class="text-center">{{ $sale_product->pivot->batch_no }}</td>
                                                <td class="unit-name text-center"></td>
                                                <td class="text-center">{{ $stock_qty }}</td>
                                                <td><input type="text" class="form-control qty text-center" name="products[{{ $key + 1 }}][qty]" id="products_{{ $key + 1 }}_qty" value="{{ number_format($sale_product->pivot->qty,2,'.','') }}"></td>
                                                <td class="text-right">{{ $product_price }}</td>
                                                <td class="tax text-right">{{ number_format((float)$sale_product->pivot->tax, 2, '.','') }}</td>
                                                <td class="sub-total text-right">{{ number_format((float)$sale_product->pivot->total, 2, '.','') }}</td>
                                                <td class="text-center"><button type="button" class="btn btn-danger btn-md remove-product"><i class="fas fa-trash"></i></button></td>
                                                <input type="hidden" class="product-id" name="products[{{ $key + 1 }}][id]"  value="{{ $sale_product->pivot->product_id }}">
                                                <input type="hidden" class="product-code" name="products[{{ $key + 1 }}][code]" value="{{ $sale_product->code }}" data-row="{{ $key + 1 }}">
                                                <input type="hidden" class="batch-no" name="products[{{ $key + 1 }}][batch_no]" id="products_{{ $key + 1 }}_batch_no" value="{{ $sale_product->pivot->batch_no }}">
                                                <input type="hidden"   class="stock-qty form-control text-center" name="products[{{ $key+1 }}][stock_qty]"  value="{{ $stock_qty }}">
                                                <input type="hidden" class="product-price" name="products[{{ $key+1 }}][net_unit_price]" value="{{ $product_price }}">
                                                <input type="hidden" class="sale-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                <input type="hidden" class="sale-unit-operator"  value="{{ $unit_operator }}">
                                                <input type="hidden" class="sale-unit-operation-value"  value="{{ $unit_operation_value }}">
                                                <input type="hidden" class="tax-rate" name="products[{{ $key+1 }}][tax_rate]" value="{{ $sale_product->pivot->tax_rate }}">
                                                @if ($tax)
                                                <input type="hidden" class="tax-name" value="{{ $tax->name }}">
                                                @else
                                                <input type="hidden" class="tax-name" value="No Tax">
                                                @endif
                                                <input type="hidden" class="tax-method" value="{{ $sale_product->tax_method }}">
                                                <input type="hidden" class="tax-value" name="products[{{ $key+1 }}][tax]" value="{{ $sale_product->pivot->tax }}">
                                                <input type="hidden" class="subtotal-value" name="products[{{ $key+1 }}][subtotal]" value="{{ $sale_product->pivot->total }}">
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ number_format($sale->total_qty,2,'.','') }}</th>
                                        <th></th>
                                        <th id="total-tax" class="text-right font-weight-bolder">{{ number_format($sale->total_tax,2,'.','') }}</th>
                                        <th id="total" class="text-right font-weight-bolder">{{ number_format($sale->total_price,2,'.','') }}</th>
                                        <th></th>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <div class="row justify-content-between">
                                    <x-form.selectbox labelName="Order Tax" name="order_tax_rate" col="col-md-2" class="selectpicker">
                                        <option value="0" selected>No Tax</option>
                                        @if (!$taxes->isEmpty())
                                            @foreach ($taxes as $tax)
                                                <option value="{{ $tax->rate }}" {{ $sale->order_tax_rate == $tax->rate ? 'selected' : '' }}>{{ $tax->name }}</option>
                                            @endforeach
                                        @endif
                                    </x-form.selectbox>

                                    <div class="form-group col-md-2">
                                        <label for="order_discount">Order Discount</label>
                                        <input type="text" class="form-control" name="order_discount" id="order_discount" value="{{ number_format($sale->order_discount,2,'.','') }}">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="shipping_cost">Shipping Cost</label>
                                        <input type="text" class="form-control" name="shipping_cost" id="shipping_cost"  value="{{ number_format($sale->shipping_cost,2,'.','') }}" />
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="labor_cost">Labor Cost</label>
                                        <input type="text" class="form-control" name="labor_cost" id="labor_cost"  value="{{ number_format($sale->labor_cost,2,'.','') }}" />
                                    </div>

                                    <x-form.selectbox labelName="Payment Status" name="payment_status" required="required"  col="col-md-2" class="selectpicker">
                                        @foreach (PAYMENT_STATUS as $key => $value)
                                        <option value="{{ $key }}" {{ $sale->payment_status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>
                                </div>
                            </div>
                            
                            
                            <div class="form-group col-md-12">
                                <label for="note">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3">{{ $sale->note }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">0(0)</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">0.00</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">0.00</span></th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">0.00</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">0.00</span></th>
                                        <th><strong>Labor Cost</strong><span class="float-right" id="labor_total_cost">0.00</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                    </thead>
                                </table>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty" value="{{ $sale->total_qty }}">
                                <input type="hidden" name="total_discount" value="{{ $sale->total_discount }}">
                                <input type="hidden" name="total_tax" value="{{ $sale->total_tax }}">
                                <input type="hidden" name="total_price" value="{{ $sale->total_price }}">
                                <input type="hidden" name="item" value="{{ $sale->item }}">
                                <input type="hidden" name="order_tax" value="{{ $sale->order_tax }}">
                                <input type="hidden" name="grand_total" value="{{ $sale->grand_total }}">
                            </div>
                            <div class="payment col-md-12 @if($sale->payment_status == 3) d-none @endif">
                                <div class="row">
                                    <div class="form-group col-md-4 required">
                                        <label for="previous_due">Previous Due</label>
                                        <input type="text" class="form-control" name="previous_due" id="previous_due" value="{{ $sale->previous_due }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4 required">
                                        <label for="net_total">Net Total</label>
                                        <input type="text" class="form-control" name="net_total" id="net_total" value="{{ ($sale->grand_total + $sale->previous_due) }}" readonly>
                                    </div>
                                    <div class="form-group col-md-4 required">
                                        <label for="paid_amount">Paid Amount</label>
                                        <input type="text" class="form-control" name="paid_amount" id="paid_amount" value="{{$sale->paid_amount }}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="due_amount">Due Amount</label>
                                        <input type="text" class="form-control" name="due_amount" id="due_amount" value="{{$sale->due_amount }}" readonly>
                                    </div>
                                    <x-form.selectbox labelName="Payment Method" name="payment_method" onchange="account_list(this.value)" required="required"  col="col-md-4" class="selectpicker">
                                        @foreach (SALE_PAYMENT_METHOD as $key => $value)
                                        <option value="{{ $key }}" @if($sale->payment_method) {{ $sale->payment_method == $key ? 'selected' : '' }} @endif>{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Account" name="account_id" required="required"  col="col-md-4" class="selectpicker"/>
                                    <div class="form-group required col-md-4 @if($sale->payment_method) {{ $sale->payment_method != 1  ? '' : 'd-none' }} @endif reference_no">
                                        <label for="reference_no">Reference No</label>
                                        <input type="text" class="form-control" name="reference_no" id="reference_no" value="{{ $sale->reference_no }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-grou col-md-12 text-center pt-5">
                                <a href="{{ url('sale') }}" class="btn btn-danger btn-sm mr-3"><i class="far fa-window-close"></i> Cancel</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="update_data()"><i class="fas fa-save"></i> Update</button>
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
<script src="js/jquery-ui.js"></script>
<script src="js/moment.js"></script>
<script src="js/bootstrap-datetimepicker.min.js"></script>
<script>

$(document).ready(function () {

    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

    $('#product_code_name').on('input',function(){
        var customer_id  = $('#customer_id_hidden').val();
        var temp_data = $('#product_code_name').val();
        if(!customer_id){
            $('#product_code_name').val(temp_data.substring(0,temp_data.length - 1));
            notification('error','Please select customer');
        }
    });
    //array data depend on warehouse
    var product_array = [];
    var product_code  = [];
    var product_name  = [];
    var product_qty   = [];

    // array data with selection
    var product_price        = [];
    var tax_rate             = [];
    var tax_name             = [];
    var tax_method           = [];
    var unit_name            = [];
    var unit_operator        = [];
    var unit_operation_value = [];

    //temporary array
    var temp_unit_name            = [];
    var temp_unit_operator        = [];
    var temp_unit_operation_value = [];

    var rowindex;
    var customer_group_rate;
    var row_product_price;

    var rownumber = $('#product_table tbody tr:last').index();

    for (rowindex = 0; rowindex <= rownumber; rowindex++) {
        
        product_price.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.product-price').val()));
        var quantity = parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.qty').val())
        product_qty.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.stock-qty').val()));
        tax_rate.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-rate').val()));
        tax_name.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-name').val());
        tax_method.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-method').val());
        temp_unit_name = $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val().split(',');
        unit_name.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val());
        unit_operator.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit-operator').val());
        unit_operation_value.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit-operation-value').val());
        $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val(temp_unit_name[0]);
        $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.unit-name').text(temp_unit_name[0]);
    }

    //assigning value

    $('#item').text($('input[name="item"]').val() + '('+$('input[name="total_qty"]').val()+')');
    $('#subtotal').text(parseFloat($('input[name="total_price"]').val()).toFixed(2));
    $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed(2));

    if(!$('input[name="order_discount"]').val())
    {
        $('input[name="order_discount"]').val('0.00');
    }
    $('#order_total_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed(2));
    if(!$('input[name="shipping_cost"]').val())
    {
        $('input[name="shipping_cost"]').val('0.00');
    }
    $('#shipping_total_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed(2));
    if(!$('input[name="labor_cost"]').val())
    {
        $('input[name="labor_cost"]').val('0.00');
    }
    $('#labor_total_cost').text(parseFloat($('input[name="labor_cost"]').val()).toFixed(2));
    $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));


    var cid = $('input[name="customer_id_hidden"]').val();
    $.get('{{ url("customer/group-data") }}/'+cid,function(data){
        customer_group_rate = (data/100);
    });

    //Get customer group rate for special price
    $('#customer_id').on('change',function(){
        var id = $(this).val();
        $.get('{{ url("customer/group-data") }}/'+id,function(data){
            customer_group_rate = (data/100);
        });
        $.get('{{ url("customer/previous-balance") }}/'+id,function(data){
            $('#previous_due').val(parseFloat(data).toFixed(2));
        });
    });
    
    //Search product by name or barcode
    $('#product_code_name').autocomplete({
        source: function( request, response ) {
          $.ajax({
            url:"{{url('sale/product-autocomplete-search')}}",
            type: 'post',
            dataType: "json",
            data: {
               _token: _token,
               search: request.term
            },
            success: function( data ) {
               response( data );
            }
          });
        },
        minLength: 3,
        response: function(event, ui) {
            if (ui.content.length == 1) {
                var data = ui.content[0];
                $(this).autocomplete( "close" );
                product_search(data);
            };
        },
        select: function (event, ui) {
            var data = ui.item;
            product_search(data);
        },
    }).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $("<li class='ui-autocomplete-row'></li>")
            .data("item.autocomplete", item)
            .append(item.label)
            .appendTo(ul);
    };

    //Update product qty
    $('#product_table').on('keyup','.qty',function(){
        rowindex = $(this).closest('tr').index();
        if($(this).val() < 1 && $(this).val() != ''){
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
            notification('error','Qunatity can\'t be less than 1');
        }
        checkQuantity($(this).val(),true);
    });

    //Remove product from cart table
    $('#product_table').on('click','.remove-product',function(){
        rowindex = $(this).closest('tr').index();
        product_price.splice(rowindex,1);
        tax_rate.splice(rowindex,1);
        tax_name.splice(rowindex,1);
        tax_method.splice(rowindex,1);
        unit_name.splice(rowindex,1);
        unit_operator.splice(rowindex,1);
        unit_operation_value.splice(rowindex,1);
        $(this).closest('tr').remove();
        calculateTotal();
    });

    //Add  Product to cart table
    @if (!$sale->sale_products->isEmpty())
    var count = "{{ count($sale->sale_products) + 1 }}" ;
    @else 
    var count = 1;
    @endif
    function product_search(data) {
        $.ajax({
            url: '{{ route("sale.product.search") }}',
            type: 'POST',
            data: {
                data: data,_token:_token
            },
            success: function(data) {
                var flag = 1;
                $('.product-code').each(function(i){
                    let row_index = $(this).data('row');
                    let batch_no = $(`#products_${row_index}_batch_no`).val();
                    if($(this).val() == data.code && batch_no == data.batch_no){
                        rowindex = i;
                        var qty = parseFloat($('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val()) + 1;
                        $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(qty);
                        checkQuantity(String(qty),true);
                        flag = 0;
                    }
                });
                $('#product_code_name').val('');
                if(flag)
                {
                    temp_unit_name = data.unit_name.split(',');
                    var newRow = $('<tr>');
                    var cols = '';
                    cols += `<td>${data.name}</td>`;
                    cols += `<td class="text-center">${data.code}</td>`;
                    cols += `<td class="text-center">${data.batch_no}</td>`;
                    cols += `<td class="unit-name text-center"></td>`;
                    cols += `<td class="text-center">${data.qty}</td>`;
                    cols += `<td><input type="text" class="form-control qty text-center" name="products[${count}][qty]" id="products_${count}_qty" value="1"></td>`;
                    cols += `<td class="text-right">${data.price}</td>`;
                    cols += `<td class="tax text-right"></td>`;
                    cols += `<td class="sub-total text-right"></td>`;
                    cols += `<td class="text-center"><button type="button" class="btn btn-danger btn-md remove-product"><i class="fas fa-trash"></i></button></td>`;
                    cols += `<input type="hidden" class="product-id" name="products[${count}][id]"  value="${data.id}">`;
                    cols += `<input type="hidden" class="product-code" name="products[${count}][code]" value="${data.code}" data-row="${count}">`;
                    cols += `<input type="hidden" class="batch-no" name="products[${count}][batch_no]" id="products_${count}_batch_no" value="${data.batch_no}">`;
                    cols += `<input type="hidden" class="product-unit" name="products[${count}][unit]" value="`+temp_unit_name[0]+`">`;
                    cols += `<input type="hidden" class="stock-qty" name="products[${count}][stock_qty]" id="products_${count}_stock_qty"  value="${data.qty}">`;
                    cols += `<input type="hidden" class="net-unit-price" name="products[${count}][net_unit_price]" id="products_${count}_net_unit_price" value="${data.price}">`;
                    cols += `<input type="hidden" class="tax-rate" name="products[${count}][tax_rate]" value="${data.tax_rate}">`;
                    cols += `<input type="hidden" class="tax-value" name="products[${count}][tax]">`;
                    cols += `<input type="hidden" class="subtotal-value" name="products[${count}][subtotal]">`;

                    newRow.append(cols);
                    $('#product_table tbody').append(newRow);

                    product_price.push(parseFloat(data.price) + parseFloat(data.price * customer_group_rate));
                    product_qty.push(data.qty);
                    tax_rate.push(parseFloat(data.tax_rate));
                    tax_name.push(data.tax_name);
                    tax_method.push(data.tax_method);
                    unit_name.push(data.unit_name);
                    unit_operator.push(data.unit_operator);
                    unit_operation_value.push(data.unit_operation_value);
                    rowindex = newRow.index();
                    checkQuantity(1,true);
                    count++;
                }
            }
        });
    }

    function checkQuantity(sale_qty,flag)
    {

        var operator = unit_operator[rowindex].split(',');
        var operation_value = unit_operation_value[rowindex].split(',');

        if(operator[0] == '*')
        {
            total_qty = sale_qty * operation_value[0];
        }else if(operator[0] == '/'){
            total_qty = sale_qty / operation_value[0];
        }
        if(total_qty > parseFloat(product_qty[rowindex])){
            notification('error','Quantity exceed stock quantity');
            if(flag)
            {
                sale_qty = sale_qty.substring(0,sale_qty.length - 1);
                $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(sale_qty);
            }else{
                return;
            }
        }

        if(!flag)
        {
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(sale_qty);
        }
        calculateProductData(sale_qty);
    }


    function calculateProductData(quantity){ 
        unitConversion();

        // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(8)').text((product_discount[rowindex] * quantity).toFixed(2));
        // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.unit-name').text(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));

        if(tax_method[rowindex] == 1)
        {
            var net_unit_price = row_product_price - 0;
            var tax = net_unit_price * quantity * (tax_rate[rowindex]/100);
            var sub_total = (net_unit_price * quantity) + tax;
        }else{
            var sub_total_unit = row_product_price - 0;
            var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
            var tax = (sub_total_unit - net_unit_price) * quantity;
            var sub_total = sub_total_unit * quantity;
        }

        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(7)').text(net_unit_price.toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.net-unit-price').val(net_unit_price.toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(8)').text(tax.toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-value').val(tax.toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').text(sub_total.toFixed(2));
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.subtotal-value').val(sub_total.toFixed(2));

        calculateTotal();
    }

    function unitConversion()
    {
        var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
        row_unit_operation_value = parseFloat(row_unit_operation_value);
        if(row_unit_operator == '*')
        {
            row_product_price = product_price[rowindex] * row_unit_operation_value;
        }else{
            row_product_price = product_price[rowindex] / row_unit_operation_value;
        }
    }

    function calculateTotal()
    {
        //sum of qty
        var total_qty = 0;
        $('.qty').each(function() {
            if($(this).val() == ''){
                total_qty += 0;
            }else{
                total_qty += parseFloat($(this).val());
            }
        });
        $('#total-qty').text(total_qty);
        $('input[name="total_qty"]').val(total_qty);

        //sum of tax
        var total_tax = 0;
        $('.tax').each(function() {
            total_tax += parseFloat($(this).text());
        });
        $('#total-tax').text(total_tax.toFixed(2));
        $('input[name="total_tax"]').val(total_tax.toFixed(2));

        //sum of subtotal
        var total = 0;
        $('.sub-total').each(function() {
            total += parseFloat($(this).text());
        });
        $('#total').text(total.toFixed(2));
        $('input[name="total_price"]').val(total.toFixed(2));

        calculateGrandTotal();
    }

    function calculateGrandTotal()
    {
        var item           = $('#product_table tbody tr:last').index();
        var total_qty      = parseFloat($('#total-qty').text());
        var subtotal       = parseFloat($('#total').text());
        var order_tax      = parseFloat($('select[name="order_tax_rate"]').val());
        var order_discount = parseFloat($('#order_discount').val());
        var shipping_cost  = parseFloat($('#shipping_cost').val());
        var labor_cost     = parseFloat($('#labor_cost').val());

        if(!order_discount){
            order_discount = 0.00;
        }
        if(!shipping_cost){
            shipping_cost = 0.00;
        }
        if(!labor_cost){
            labor_cost = 0.00;
        }

        item = ++item + '(' + total_qty + ')';
        order_tax = (subtotal - order_discount) * (order_tax / 100);
        var grand_total = (subtotal + order_tax + shipping_cost + labor_cost) - order_discount;
        var previous_due = parseFloat($('#previous_due').val());
        var net_total = grand_total + previous_due;

        $('#item').text(item);
        $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
        $('#subtotal').text(subtotal.toFixed(2));
        $('#order_total_tax').text(order_tax.toFixed(2));
        $('input[name="order_tax"]').val(order_tax.toFixed(2));
        $('#order_total_discount').text(order_discount.toFixed(2));
        $('#shipping_total_cost').text(shipping_cost.toFixed(2));
        $('#labor_total_cost').text(labor_cost.toFixed(2));
        $('#grand_total').text(grand_total.toFixed(2));
        $('input[name="grand_total"]').val(grand_total.toFixed(2));
        $('input[name="net_total"]').val(net_total.toFixed(2));
        if($('#payment_status option:selected').val() == 1)
        {
            $('#paid_amount').val(net_total.toFixed(2));
            $('#due_amount').val(parseFloat(0).toFixed(2));
        }else if($('#payment_status option:selected').val() == 2){
            var paid_amount = $('#paid_amount').val();
            $('#due_amount').val(parseFloat(net_total-paid_amount).toFixed(2));
        }else{
            $('#due_amount').val(parseFloat(net_total).toFixed(2));
        }
    }

    $('input[name="order_discount"]').on('input',function(){
        if(parseFloat($(this).val()) > parseFloat($('input[name="grand_total"]').val()))
        {
            notification('error','Order discount can\'t exceed grand total amount');
            $('input[name="order_discount"]').val(parseFloat(0));
        }
        calculateGrandTotal();

    });
    $('input[name="shipping_cost"]').on('input',function(){
        calculateGrandTotal();
    });
    $('input[name="labor_cost"]').on('input',function(){
        calculateGrandTotal();
    });
    $('select[name="order_tax_rate"]').on('change',function(){
        calculateGrandTotal();
    });

    $('#payment_status').on('change',function(){
        if($(this).val() != 3){
            $('.payment').removeClass('d-none');
            $('#paid_amount').val($('input[name="net_total"]').val());
            $('#due_amount').val(parseFloat(0).toFixed(2));
        }else{
            $('#paid_amount').val(0);
            $('#due_amount').val(parseFloat($('input[name="net_total"]').val()).toFixed(2));
            $('.payment').addClass('d-none');
        }
    });

    $('#payment_method').on('change',function(){
        if($(this).val() != 1){
            $('.reference_no').removeClass('d-none');
        }else{
            $('.reference_no').addClass('d-none');
        }
    });

    $('#paid_amount').on('input',function(){
        var payable_amount = parseFloat($('input[name="net_total"]').val());
        var paid_amount = parseFloat($(this).val());
        
        if(paid_amount > payable_amount){
            $('#paid_amount').val(payable_amount.toFixed(2));
            notification('error','Paid amount cannot be bigger than net total amount');
        }
        $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));
        
    });
});

account_list("{{ $sale->payment_method }}","{{ $sale->account_id }}");
function account_list(payment_method,account_id='')
{
    $.ajax({
        url: "{{route('account.list')}}",
        type: "POST",
        data: { payment_method: payment_method,_token: _token},
        success: function (data) {
            $('#sale_update_form #account_id').html('');
            $('#sale_update_form #account_id').html(data);
            $('#sale_update_form #account_id.selectpicker').selectpicker('refresh');
            if(account_id)
            {
                $('#sale_update_form #account_id').val(account_id);
                $('#sale_update_form #account_id.selectpicker').selectpicker('refresh');
            }
        },
        error: function (xhr, ajaxOption, thrownError) {
            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
        }
    });
}



function update_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to order table!")
    }else{
        let form = document.getElementById('sale_update_form');
        let formData = new FormData(form);
        let url = "{{route('sale.update')}}";
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
                        window.location.replace("{{ route('sale') }}");
                        
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