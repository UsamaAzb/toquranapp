<!-- Edit User Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">Edit User Information</h4>
          <p>Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="row g-6"
      data-action="{{ route('admin.students.modal-update', $student->id) }}">
      @csrf
  <input type="hidden" name="_method" value="PUT">
          <h5 class="text-primary-custom mb-3">
            <i class="fas fa-user me-2"></i>Parent Information
        </h5>
          <div class="col-12 col-md-6">
            <label class="form-label" for="parent_first_name">First Name</label>
            <input type="text" id="parent_first_name" name="parent[first_name]" class="form-control" placeholder="" value="{{$student->parent->first_name ?? ''}}" />
            <div class="invalid-feedback" data-error-for="parent.first_name"></div>

          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="parent_last_name">Last Name</label>
            <input type="text" id="parent_last_name" name="parent[last_name]" class="form-control" placeholder="" value="{{$student->parent->last_name ?? ''}}" />
            <div class="invalid-feedback" data-error-for="parent.last_name"></div>

          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="parent_email">Email</label>
            <input type="text" id="parent_email" name="parent[email]" class="form-control" placeholder="" value="{{$student->parent->email ?? ''}}" />
            <div class="invalid-feedback" data-error-for="parent.email"></div>

          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="parent_phone">Phone</label>
            <input type="text" id="parent_phone" name="parent[phone]" class="form-control" placeholder="" value="{{$student->parent->phone ?? ''}}" />
            <div class="invalid-feedback" data-error-for="parent.phone"></div>

          </div>
          <h5 class="text-primary-custom mb-3">
           <i class="fas fa-child me-2"></i>Child Information
       </h5>

          <div class="col-12 col-md-6">
            <label class="form-label" for="student_first_name">First Name</label>
            <input type="text" id="student_first_name" name="student[first_name]" class="form-control" placeholder="" value="{{ $student->first_name ?? '' }} " />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="student_last_name">Last Name</label>
            <input type="text" id="student_last_name" name="student[last_name]" class="form-control" placeholder="" value="{{$student->last_name ?? ''}}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="student_age">Age</label>
            <input type="text" id="student_age" name="student[age]" class="form-control" placeholder="" value="{{$student->age ?? ''}}" />
          </div>
          <div class="col-12 col-md-6">
          <label for="student_school_system" class="form-label">School System </label>
          <select class="form-select " id="student_school_system" name="student[school_system]">
              <option value="">Select School System</option>
              @foreach (\App\Support\SchoolSystemOptions::labels() as $schoolSystemValue => $schoolSystemLabel)
                <option value="{{ $schoolSystemValue }}" {{ $student->school_system == $schoolSystemValue ? 'selected' : '' }}>{{ $schoolSystemLabel }}</option>
              @endforeach
          </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="student_program_id">Program </label>
        <select id="student_program_id" name="student[program_id]" class=" form-select" >
         <option value="">Select program</option>
         @foreach($programs as $program)
             <option value="{{ $program->id }}" {{ $student->program_id   == $program->id ? 'selected' : '' }}>{{ $program->title }} </option>
         @endforeach
        </select>
      </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="student_grade_level_id">Grade</label>
            <select id="student_grade_level_id" name="student[grade_level_id]" class=" form-select" >
             <option value="">Select Grade</option>
             @foreach($grade_levels as $grade_level)
                 <option value="{{ $grade_level->id }}" {{  $student->grade_level_id  == $grade_level->id ? 'selected' : '' }}>{{ $grade_level->title }} </option>
             @endforeach
            </select>
            <div class="invalid-feedback" data-error-for="student.grade_level_id"></div>

          </div>
          <div class="col-12 col-md-6">
          <label for="service_interest" class="form-label" for="student_service_type_id"> service interest</label>
             <select class="form-select" id="student_service_type_id" name="student[service_type_id]" required>
                 <option value="">Select a service</option>
                 @foreach($services_types as $services_type)
                  <option @if($services_type->id==5 ||$services_type->id==6 ) disabled @endif value="{{$services_type->id}}" {{  $student->service_type_id  == $services_type->id ? 'selected' : '' }}>
                     {{$services_type->title}}
                 </option>
                 @endforeach
             </select>
             <div class="invalid-feedback" data-error-for="student.service_type_id"></div>
           </div>
           <div class="col-12 col-md-6">
             <label class="form-label" for="student_status">Status</label>
             <select id="student_status" name="student[status]" class="form-select" aria-label="Default select example">
               <option selected>Status</option>
               <option value="active" {{ $student->status  == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{  $student->status  == 'inactive' ? 'selected' : '' }}>Inactive</option>
   </select>
             <div class="invalid-feedback" data-error-for="student.status"></div>

           </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3" id="editUserSubmit">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->


<script>
(function () {
  function dotToBracket(name) { return name.replace(/\.(\w+)/g, '[$1]'); }

  function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; el.style.display = ''; });
  }

  function showErrors(form, errors) {
    Object.entries(errors || {}).forEach(([key, msgs]) => {
      const selector = `[name="${dotToBracket(key)}"]`;
      const input = form.querySelector(selector);
      if (input) input.classList.add('is-invalid');
      const box = form.querySelector(`[data-error-for="${key}"]`);
      if (box) { box.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs); box.style.display = 'block'; }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const form  = document.getElementById('editUserForm');
    const modal = document.getElementById('editUser');
    if (!form) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const action = form.dataset.action;

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearErrors(form);

      const fd = new FormData(form);
      // ensure method spoofing even if someone removed the hidden input
      if (!fd.has('_method')) fd.append('_method', 'PUT');

      try {
        const res = await fetch(action, {
          method: 'POST', // spoofed as PUT by _method
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
          },
          body: fd
        });

        const payload = await res.json().catch(() => ({}));

        if (!res.ok) {
          if (payload.errors) { showErrors(form, payload.errors); return; }
          alert(payload.message || 'Update failed.');
          return;
        }
        if (!res.ok) {
           console.log('422 errors:', payload?.errors);
           showErrors(form, payload.errors); return; }

        // Success
        if (window.bootstrap && modal) {
          const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
          bsModal.hide();
        }
        // Option A: update visible fields without reload (recommended)
        // document.querySelector('[data-bind="parent_name"]').textContent = `${fd.get('parent[first_name]') ?? ''} ${fd.get('parent[last_name]') ?? ''}`.trim();

        // Option B: reload
        location.reload();
      } catch (err) {
        console.error(err);
        alert('Network error. Please try again.');
      }
    });

    // If modal is opened dynamically later, ensure handler is still bound
    if (modal) {
      modal.addEventListener('shown.bs.modal', function () {
        clearErrors(form);
      });
    }
  });
})();
</script>
