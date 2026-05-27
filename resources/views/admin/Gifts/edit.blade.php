@extends('layouts/layoutMaster')

@section('title', 'Validation - Forms')
@section('content')

  <div class="row g-6 mb-6  justify-content-center">
    <!-- Bootstrap Validation -->
    <div class="col-md-10 ">
      <div class="card">
        <h5 class="card-header">ِAdd New Gift</h5>
        <div class="card-body">
          <form class ="row" action="{{ route('gifts.update', $gift) }}" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')

                        <div class="col-md-6 form-control-validation mb-4">
                          <label class="form-label" for="title"> Name</label>
                          <input type="text" id="title" class="form-control" placeholder="Cape"
                            name="title" value="{{ old('title', $gift->title) }}"  />
                            @error('title')
                               <div class="text-danger small">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 form-control-validation mb-4">
                          @if($gift->image_path)
                          <label class="form-label">Current Image</label><br>
                          @else
                          <label class="form-label"> Image</label><br>
                            @endif


                          <!-- <label for="formValidationFile" for="image" class="form-label">Gift Image</label> -->
                          <input class="form-control" type="file" id="image" name="image" />
                          @if($gift->image_path)
                              <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($gift->image_path, '/')) }}" width="90" class="mb-2">
                          @endif
                          @error('image')
                             <div class="text-danger small">{{ $message }}</div>
                         @enderror
                        </div>
                        <div class="col-md-6 form-control-validation mb-4">
                          <label class="form-label" for="description">description</label>
                          <textarea rows="4" class="form-control" type="text" id="description" name="description"
                           >{{ old('description', $gift->description) }}</textarea>
                           @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3 col-md-8 ms-5">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                   {{ old('is_active', $gift->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                        <div class="d-flex justify-content-between  ">
                            <div class="">
                          <a href="{{url('admin/gifts')}}" type="button" class="btn btn-secondary" >Cancel</a>
            </div>
                          <div class="">
                            <button type="submit" class="btn btn-primary">Submit</button>
                          </div>
                        </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /Bootstrap Validation -->
  </div>
@endsection
