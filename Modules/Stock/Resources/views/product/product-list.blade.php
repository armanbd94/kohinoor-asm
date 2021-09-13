@php
    $grand_total = 0;
@endphp
@if (!$warehouses->isEmpty())
    @foreach ($warehouses as $index => $warehouse)
        @if (!$warehouse->products->isEmpty())
            <div class="col-md-12 text-center"><h3 class="py-3 bg-warning text-white" style="max-width:500px;margin: 50px auto 10px auto;">{{ $product->name }}</h3></div>
            @php 
            $total_stock_value = 0; 
            @endphp
                
            <table id="dataTable" class="table table-bordered table-hover mb-5">
                <thead class="bg-primary">
                    <tr>
                        <th class="text-center">Batch No</th>
                        <th class="text-center">Stock Unit</th>
                        <th class="text-center">Stock Base Unit</th>
                        <th class="text-right">Net Unit Price</th>
                        <th class="text-right">Base Unit Price</th>
                        <th class="text-center">Unit Stock Qty</th>
                        <th class="text-center">Base Unit Stock Qty</th>
                        <th class="text-right">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $total_unit_qty = 0; 
                        $total_base_unit_qty = 0; 
                        $total = 0; 
                    @endphp
                    @if($product_id)
                        @foreach ($warehouse->products as $key => $item)
                            @if($product_id == $item->id && $item->pivot->qty > 0)
                                @php
                                    $unit_qty = 0;
                                    $unit_price = 0;
                                    if($item->unit->operator == '*')
                                    {
                                        $qty_unit = $item->pivot->qty / $item->unit->operation_value;
                                        $unit_cost = $item->unit->operation_value * $item->base_unit_price;
                                    }else{
                                        $qty_unit = $item->pivot->qty * $item->unit->operation_value;
                                        $unit_cost = $item->unit->operation_value / $item->base_unit_price;
                                    }
                                    $total_unit_qty += $qty_unit;
                                    $total_base_unit_qty += $item->pivot->qty;
                                    $total += ($item->pivot->qty * $item->base_unit_price);
                                @endphp
                                <tr>
                                    <td>{{ $item->pivot->batch_no }}</td>
                                    <td class="text-center">{{ $item->unit->unit_name.' ('.$item->unit->unit_code.')' }}</td>
                                    <td class="text-center">{{ $item->base_unit->unit_name.' ('.$item->base_unit->unit_code.')' }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price,2,'.','') }}</td>
                                    <td class="text-right">{{ number_format($item->base_unit_price,2,'.','') }}</td>
                                    <td class="text-center">{{ $qty_unit }}</td>
                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                    <td class="text-right">{{ number_format(($item->pivot->qty * $item->base_unit_price),2,'.','') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        @foreach ($warehouse->products as $key => $item)
                            @if($item->pivot->qty > 0)
                                @php
                                    $unit_qty = 0;
                                    $unit_price = 0;
                                    if($item->unit->operator == '*')
                                    {
                                        $qty_unit = $item->pivot->qty / $item->unit->operation_value;
                                        $unit_cost = $item->unit->operation_value * $item->base_unit_price;
                                    }else{
                                        $qty_unit = $item->pivot->qty * $item->unit->operation_value;
                                        $unit_cost = $item->unit->operation_value / $item->base_unit_price;
                                    }
                                    $total_unit_qty += $qty_unit;
                                    $total_base_unit_qty += $item->pivot->qty;
                                    $total += ($item->pivot->qty * $item->base_unit_price);
                                @endphp
                                <tr>
                                    <td>{{ $item->pivot->batch_no }}</td>
                                    <td class="text-center">{{ $item->unit->unit_name.' ('.$item->unit->unit_code.')' }}</td>
                                    <td class="text-center">{{ $item->base_unit->unit_name.' ('.$item->base_unit->unit_code.')' }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price,2,'.','') }}</td>
                                    <td class="text-right">{{ number_format($item->base_unit_price,2,'.','') }}</td>
                                    <td class="text-center">{{ $qty_unit }}</td>
                                    <td class="text-center">{{ $item->pivot->qty }}</td>
                                    <td class="text-right">{{ number_format(($item->pivot->qty * $item->base_unit_price),2,'.','') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-primary">
                        <th colspan="5" style="text-align: right !important;font-weight:bold;color:white;">Total</th>
                        <th style="text-align: center !important;font-weight:bold;color:white;">{{ number_format($total_unit_qty,2,'.','') }}</th>
                        <th style="text-align: center !important;font-weight:bold;color:white;">{{ number_format($total_base_unit_qty,2,'.','') }}</th>
                        <th style="text-align: right !important;font-weight:bold;color:white;">{{ number_format($total,2,'.','') }}</th>
                        @php
                        $total_stock_value += $total;
                    @endphp
                    </tr>
                </tfoot>
            </table>
            @php
                $grand_total += $total_stock_value;
            @endphp
        @endif
    @endforeach
    <h3 class="bg-dark text-white font-weight-bolder p-3 text-right">Total Stock Value = {{ number_format($grand_total,2,'.','') }}</h3>
@else 
    <div class="col-md-12 text-center"><h3 class="py-3 bg-danger text-white">Stock Data is Empty</h3></div>
@endif