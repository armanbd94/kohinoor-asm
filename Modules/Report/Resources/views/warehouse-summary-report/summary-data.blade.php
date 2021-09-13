<!-- ## Total Inventory Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h2 class="card-label text-white">Total Inventory Grand Value</h2>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($total_stock_value,2,'.',',') }}Tk</h5>
            @if($total_stock_value > 0)
            <h5>{{ numberTowords($total_stock_value) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Product Sales Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h2 class="card-label text-white">Product Sales Grand Value</h2>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
                <h5>{{ number_format($product_sale_data->product_sales_grand_value,2,'.',',') }}Tk</h5>
                @if($product_sale_data->product_sales_grand_value > 0)
                <h5>{{ numberTowords($product_sale_data->product_sales_grand_value) }} Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
            
        </div>
    </div>
</div>

<!-- ## Sales Collection Received Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Sales Collection Received Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
                <h5>{{ number_format($product_sale_data->sales_collection_received_value,2,'.',',') }}Tk</h5>
                @if($product_sale_data->sales_collection_received_value > 0)
                <h5>{{ numberTowords($product_sale_data->sales_collection_received_value) }} Taka</h5>
                @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Product Sales Due Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Product Sales Due Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if ($product_sale_data)
            <h5>{{ number_format($product_sale_data->product_sales_due_value,2,'.',',') }}Tk</h5>
            @if($product_sale_data->product_sales_due_value > 0)
            <h5>{{ numberTowords($product_sale_data->product_sales_due_value) }} Taka</h5>
            @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Total Due Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Total Due Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            @php $total_due = 0; @endphp
            @if (!$total_due_grand_values->isEmpty())
            @foreach ($total_due_grand_values->chunk(10) as $chunk)
                    @foreach ($chunk as $value)
                    @php $total_due += $value->due_amount; @endphp
                    @endforeach
            @endforeach
            @endif
            <h5>{{ number_format($total_due,2,'.',',') }}Tk</h5>
            @if($total_due > 0)
            <h5>{{ numberTowords($total_due) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Damage Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Damage Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($total_damage_value,2,'.',',') }}Tk</h5>
            @if($total_damage_value > 0)
            <h5>{{ numberTowords($total_damage_value) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Collection Transfer Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Collection Transfer Value</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($collection_transfer_value,2,'.',',') }}Tk</h5>
            @if($collection_transfer_value > 0)
            <h5>{{ numberTowords($collection_transfer_value) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Coupon Received -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Coupon Received</h3>
            </div>
        </div>
        <div class="card-body">
            @if($coupon_data)
            <h5>{{ trans_choice('file.piece', $coupon_data->total_coupon_received, ['value' => $coupon_data->total_coupon_received]) }}</h5>
            @else
            <h5>{{ trans_choice('file.piece', 0, ['value' => 0]) }}</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Coupon Payment Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Coupon Payment Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if($coupon_data)
            <h5>{{ number_format($coupon_data->coupon_payment_grand_value,2,'.',',') }}Tk</h5>
            @if ($coupon_data->coupon_payment_grand_value > 0)
            <h5>{{ numberTowords($coupon_data->coupon_payment_grand_value) }} Taka</h5>
            @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
            
        </div>
    </div>
</div>

<!-- ## Warehouse Expense Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Warehouse Expense Value</h3>
            </div>
        </div>
        <div class="card-body">
            <h5>{{ number_format($warehouse_expense,2,'.',',') }}Tk</h5>
            @if($warehouse_expense > 0)
            <h5>{{ numberTowords($warehouse_expense) }} Taka</h5>
            @else
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>

<!-- ## Customer Discount Grand Value -->
<div class="col-md-12 mb-5">
    <div class="card card-custom card-border">
        <div class="card-header bg-primary">
            <div class="card-title text-center">
                <h3 class="card-label text-white">Customer Discount Grand Value</h3>
            </div>
        </div>
        <div class="card-body">
            @if($product_sale_data)
            <h5>{{ number_format($product_sale_data->customer_discount_grand_value,2,'.',',') }}Tk</h5>
            @if($product_sale_data->customer_discount_grand_value > 0)
            <h5>{{ numberTowords($product_sale_data->customer_discount_grand_value) }} Taka</h5>
            @endif
            @else
            <h5>0.00 Tk</h5>
            <h5>Zero Taka</h5>
            @endif
        </div>
    </div>
</div>