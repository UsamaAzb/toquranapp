@extends('layouts/layoutMaster')

@section('title', 'Tables - Basic Tables')



@section('content')
  <!-- Basic Bootstrap Table -->
  @if(session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
     @endif
<div class="d-flex justify-content-end">
  <a href="{{url('admin/gifts/create')}}" class="btn add-new btn-primary mb-3" tabindex="0" aria-controls="DataTables_Table_0" type="button">
    <span>
      <i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i>

    Add New Gift

</span>
</a>
</div>
  <div class="card">
    <h5 class="card-header">Gifts List</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Image</th>
            <th>Status</th>
            <!-- <th>Description</th> -->
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
              @foreach($gifts as $gift)
          <tr>
            <td>
              <span class="fw-medium">{{ $gift->title }}</span>
            </td>
            <td>
               @if($gift->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($gift->image_path, '/')) }}" width="50">
                @endif
              </td>
            <td>
              {!! $gift->is_active
                  ? '<span class="badge bg-label-primary me-1">Active</span>'
                  : '<span class="badge bg-label-danger me-1">Inactive</span>'
              !!}
            </td>
            <!-- <td>{{ $gift->description }}</td> -->
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('gifts.edit', $gift) }}"><i class="icon-base ti tabler-pencil me-1"></i>
                    Edit</a>
                  <a class="dropdown-item btn-delete" data-id="{{ $gift->id }}" ><i class="icon-base ti tabler-trash me-1"></i>
                    Delete</a>
                </div>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <!--/ Basic Bootstrap Table -->
  <!-- Delete Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Gift</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="deleteModalBody">
          <!-- الرسالة هتيجي ديناميك -->
        </div>
        <div class="modal-footer">
          <form id="deleteForm" method="POST">
              @csrf
              @method('DELETE')
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
  document.addEventListener("DOMContentLoaded", function () {
      const deleteButtons = document.querySelectorAll('.btn-delete');
      const modalBody = document.getElementById('deleteModalBody');
      const deleteForm = document.getElementById('deleteForm');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

      deleteButtons.forEach(btn => {
          btn.addEventListener('click', function () {
              let giftId = this.getAttribute('data-id');

              fetch(`/admin/gifts/${giftId}/check-before-delete`)
                  .then(res => res.json())
                  .then(data => {
                      if (data.isUsed) {
                          modalBody.innerHTML = `
                              <p>The gift <b>${data.title}</b> is already assigned to students.</p>
                              <p>You cannot delete it, but you can set it <span class="badge bg-warning">Inactive</span>.</p>
                          `;
                          confirmDeleteBtn.style.display = "none"; // أخفي زرار الحذف
                      } else {
                          modalBody.innerHTML = `
                              <p>Are you sure you want to delete gift <b>${data.title}</b>?</p>
                          `;
                          confirmDeleteBtn.style.display = "inline-block"; // أظهر زرار الحذف
                          deleteForm.action = `/admin/gifts/${giftId}`;
                      }
                      let myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                      myModal.show();
                  });
          });
      });
  });
  </script>

@endsection
