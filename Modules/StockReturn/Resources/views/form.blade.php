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
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-custom card-border">
                                <div class="card-header bg-primary">
                                    <div class="card-title">
                                        <h3 class="card-label text-white">Return From Customer</h3>
                                    </div>
                                </div>
                                <div class="card-body">
                                   <form class="form-inline col-md-12" action="{{ url('return/sale') }}" method="get">
                                        <div class="form-group col-md-7">
                                            <label for="memo_no">Memo No:</label>
                                            <input type="text" name="memo_no"  class="form-control mx-3 col-md-10" id="memo_no" placeholder="Enter Sale Memo No" required="required">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-md">Serach</button>
                                    </form>
                                </div>
                            </div>
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
