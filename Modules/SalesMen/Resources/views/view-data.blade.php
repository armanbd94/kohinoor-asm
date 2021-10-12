<div class="col-md-12">
    <div class="row">
        <div class="table-responsive col-9">
            <table class="table table-borderless">
                <tr>
                    <td><b>Name</b></td><td><b>:</b></td><td>{{ $salesmen->name }}</td>
                    <td><b>Username</b></td><td><b>:</b></td><td>{{ $salesmen->username }}</td>
                </tr>
                <tr>
                    <td><b>Phone</b></td><td><b>:</b></td><td>{{ $salesmen->phone }}</td>
                    <td><b>Email</b></td><td><b>:</b></td><td>{!! $salesmen->email ? $salesmen->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>' !!}</td>
                </tr>
                
                <tr>
                    <td><b>NID No.</b></td><td><b>:</b></td><td>{{  $salesmen->nid_no  }}</td>
                    <td><b>Monthly Target Value</b></td><td><b>:</b></td><td>{{  number_format($salesmen->monthly_target_value,2,'.','')  }} Tk</td>
                </tr>
                <tr>
                    <td><b>Upazila</b></td><td><b>:</b></td><td>{{  $salesmen->upazila->name  }}</td>
                    <td><b>Address</b></td><td><b>:</b></td><td>{{  $salesmen->address  }}</td>
                </tr>
                <tr>
                    <td><b>Status</b></td><td><b>:</b></td><td>{!! STATUS_LABEL[$salesmen->status] !!}</td>
                    <td><b>Created By</b></td><td><b>:</b></td><td>{{  $salesmen->created_by  }}</td>
                </tr>
                <tr>
                    <td><b>Modified By</b></td><td><b>:</b></td><td>{{  $salesmen->modified_by  }}</td>
                    <td><b>Create Date</b></td><td><b>:</b></td><td>{{  $salesmen->created_at ? date(config('settings.date_format'),strtotime($salesmen->created_at)) : ''  }}</td>
                </tr>
                <tr>
                    <td><b>Modified Date</b></td><td><b>:</b></td><td>{{  $salesmen->updated_at ? date(config('settings.date_format'),strtotime($salesmen->updated_at)) : ''  }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-3 text-center">
            @if($salesmen->avatar)
                <img src="{{ ADMIN_BASE_PATH.'storage/'.SALESMEN_AVATAR_PATH.$salesmen->avatar }}" alt='{{ $salesmen->name }}' style='width:150px;'/>
            @else
                <img src="{{  ADMIN_BASE_PATH.'images/male.svg' }}"" alt='Default Image' style='width:150px;'/>
            @endif
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <h6 class="bg-primary text-center text-white" style="width: 250px;padding: 5px; margin: 10px auto 5px auto;">Day wise visiting routes</h6>
        <table class="table table-bordered">
            <thead class="bg-primary">
                <th>Day</th>
                <th>Route</th>
            </thead>
            <tbody>
                @if (!$salesmen->routes->isEmpty())
                    @foreach ($salesmen->routes as $route)
                        <tr>
                            <td>{{ DAYS[$route->pivot->day] }}</td>
                            <td>{{ $route->name }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>