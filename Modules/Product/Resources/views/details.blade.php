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
                    <a href="{{ route('product') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="bg-primary text-center text-white p-3">{{ $product->name }}</h4>
                    </div>
                    @if (!empty($product->image))
                    <div class="col-md-3">
                        <img src="{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image) }}" alt="{{ $product->name }}" style="width: 100%;">
                    </div>
                    @endif
                    <div class="col-md-9 pt-5 table-responsive">
                        <table class="table table-borderless table-hover">
                            <tr>
                                <td><b>Product Type</b></td> <td class="text-center"><b>:</b></td> <td>{{ PRODUCT_TYPE[$product->product_type] }}</td>
                                <td><b>Category</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->category->name }}</td>
                            </tr>
                            <tr>
                                <td><b>Barcode Symbol</b></td> <td class="text-center"><b>:</b></td> <td>{{ BARCODE_SYMBOL[$product->barcode_symbology] }}</td>
                                <td><b>Barcode</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->code }}</td>
                            </tr>
                            <tr>
                                <td><b>Cost</b></td> <td class="text-center"><b>:</b></td> <td>BDT {{ number_format($product->cost,2) }}</td>
                                <td><b>Base Unit</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->base_unit->unit_name.'('.$product->base_unit->unit_code.')' }}</td>
                            </tr>
                            <tr>
                                <td><b>Unit</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->unit->unit_name.'('.$product->unit->unit_name.')' }}</td>
                                <td><b>MRP (Price) Base Unit</b></td> <td class="text-center"><b>:</b></td> <td>BDT {{ number_format($product->base_unit_mrp,2) }}</td>
                            </tr>
                            <tr>
                                <td><b>TP (Price) Base Unit</b></td> <td class="text-center"><b>:</b></td> <td>BDT {{ number_format($product->base_unit_price,2) }}</td>
                                <td><b>MRP (Price) Unit</b></td> <td class="text-center"><b>:</b></td> <td>BDT {{ number_format($product->unit_mrp,2) }}</td>
                            </tr>
                            <tr>
                                <td><b>TP (Price) Unit</b></td> <td class="text-center"><b>:</b></td> <td>BDT {{ number_format($product->unit_price,2) }}</td>
                                <td><b>Stock Qunatity Base Unit</b></td> <td class="text-center"><b>:</b></td> <td>{{ number_format($product->base_unit_qty,2) }}</td>
                            </tr>
                            <tr>
                                <td><b>Stock Qunatity Unit</b></td> <td class="text-center"><b>:</b></td> <td>{{ number_format($product->unit_qty,2) }}</td>
                                <td><b>Alert Quantity</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->alert_quantity }}</td>
                            </tr>
                            <tr>
                                <td><b>Tax</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->tax->rate }}%</td>
                                <td><b>Tax Method</b></td> <td class="text-center"><b>:</b></td> <td>{{ TAX_METHOD[$product->tax_method] }}</td>
                            </tr>
                            @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                            <tr>
                                <td><b>Created By</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->created_by }}</td>
                                <td><b>Created At</b></td> <td class="text-center"><b>:</b></td> <td>{{ date('j-F-Y h:i:sA',strtotime($product->created_at)) }}</td>
                            </tr>
                            @if($product->modified_by)
                            <tr>
                                <td><b>Updated By</b></td> <td class="text-center"><b>:</b></td> <td>{{ $product->modified_by }}</td>
                                <td><b>Updated At</b></td> <td class="text-center"><b>:</b></td> <td>{{ date('j-F-Y h:i:sA',strtotime($product->updated_at)) }}</td>
                            </tr>
                            @endif
                            @endif
                        </table>
                    </div>
                </div>
                <div class="card card-custom mt-5">
                    <div class="card-header">
                        <div class="card-toolbar">
                            <ul class="nav nav-bold nav-pills">
                                
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#materials">Materials</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#description">Description</a>
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="materials" role="tabpanel" aria-labelledby="materials">
                                @if (!$product->product_material->isEmpty())
                                <ol>
                                    @foreach ($product->product_material as $item)
                                        <li>{{ $item->material_name }}</li>
                                    @endforeach
                                </ol>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description">
                                @if (!empty($prduct->description))
                                    <div class="padding-top-10px text-justify">
                                        {!! $prduct->description !!}
                                    </div>
                                @endif
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
