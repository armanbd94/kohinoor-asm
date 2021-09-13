<div class="modal fade" id="delivery_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

      <!-- Modal Content -->
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header bg-primary">
          <h3 class="modal-title text-white" id="model-1"></h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close text-white" style="color: white !important;"></i>
          </button>
        </div>
        <!-- /modal header -->
        <form id="delivery_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="sale_id" id="sale_id"/>
                    <x-form.selectbox labelName="Delivery Status" name="delivery_status" required="required"  col="col-md-12" class="selectpicker">
                      @foreach (DELIVERY_STATUS as $key => $value)
                      <option value="{{ $key }}">{{ $value }}</option>
                      @endforeach
                    </x-form.selectbox>
                    <x-form.textbox labelName="Delivery Date" name="delivery_date" required="required" class="date" col="col-md-12"/>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="delivery-save-btn">Save</button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>