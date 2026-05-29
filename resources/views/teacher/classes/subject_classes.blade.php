@extends('layouts/layoutMaster')

@section('title', 'My Classes')

@section('vendor-script')
  @vite('resources/assets/vendor/libs/masonry/masonry.js')
@endsection

@section('content')
  <!-- Examples -->

  <style>
    .w14-teacher-class-grid {
      align-items: stretch;
    }

    .w14-teacher-class-card {
      position: relative;
      height: 100%;
      overflow: hidden;
      border: 1px solid rgba(108, 117, 125, 0.14);
      border-radius: 0.75rem;
      box-shadow: 0 0.45rem 1.2rem rgba(43, 50, 64, 0.08);
      transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }

    .w14-teacher-class-card::before {
      content: "";
      position: absolute;
      inset-block: 0;
      inset-inline-start: 0;
      width: 0.35rem;
      background: var(--w14-subject-accent, #2092ec);
    }

    .w14-teacher-class-card:hover,
    .w14-teacher-class-card:focus-within {
      transform: translateY(-2px);
      border-color: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 38%, transparent);
      box-shadow: 0 0.7rem 1.4rem rgba(43, 50, 64, 0.12);
    }

    [data-bs-theme="dark"] .w14-teacher-class-card,
    .dark-style .w14-teacher-class-card {
      border-color: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 24%, rgba(255, 255, 255, 0.08));
      box-shadow: 0 0.45rem 1.2rem rgba(0, 0, 0, 0.22);
    }

    [data-bs-theme="dark"] .w14-teacher-class-card:hover,
    [data-bs-theme="dark"] .w14-teacher-class-card:focus-within,
    .dark-style .w14-teacher-class-card:hover,
    .dark-style .w14-teacher-class-card:focus-within {
      box-shadow: 0 0.7rem 1.4rem rgba(0, 0, 0, 0.3);
    }

    .w14-teacher-class-card-link {
      display: flex;
      align-items: center;
      min-height: 7.8rem;
      color: inherit;
      text-decoration: none;
    }

    .w14-teacher-class-card-link:hover {
      color: inherit;
    }

    .w14-teacher-class-card .card-body {
      display: grid;
      grid-template-columns: auto minmax(0, 1fr);
      align-items: center;
      gap: 1rem;
      width: 100%;
      padding: 1.25rem 1.35rem;
    }

    .w14-teacher-class-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 3.25rem;
      height: 3.25rem;
      border-radius: 0.85rem;
      background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 13%, white);
      color: var(--w14-subject-accent, #2092ec);
      font-size: 1.65rem;
      flex: 0 0 auto;
    }

    [data-bs-theme="dark"] .w14-teacher-class-icon,
    .dark-style .w14-teacher-class-icon {
      background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 22%, #252a3d);
    }

    .w14-teacher-class-title {
      margin: 0;
      color: var(--bs-heading-color, #2b3240);
      font-size: 1.05rem;
      font-weight: 700;
      line-height: 1.28;
    }

    .w14-teacher-class-subject {
      display: block;
      margin-top: 0.4rem;
      color: var(--w14-subject-accent, #2092ec);
      font-size: 0.9rem;
      font-weight: 700;
      line-height: 1.25;
    }

    .w14-teacher-class-meta {
      display: block;
      margin-top: 0.2rem;
      color: var(--bs-secondary-color, #6c757d);
      font-size: 0.82rem;
      font-weight: 500;
      line-height: 1.25;
    }

    .w14-subject-tone-language { --w14-subject-accent: #2092ec; }
    .w14-subject-tone-wellbeing { --w14-subject-accent: #22b573; }
    .w14-subject-tone-math { --w14-subject-accent: #ff9f1c; }
    .w14-subject-tone-science { --w14-subject-accent: #7c5cff; }
    .w14-subject-tone-arts { --w14-subject-accent: #e05297; }
    .w14-subject-tone-humanities { --w14-subject-accent: #00a6a6; }
    .w14-subject-tone-default { --w14-subject-accent: #64748b; }

    @media (max-width: 575.98px) {
      .w14-teacher-class-grid {
        row-gap: 0.85rem;
      }

      .w14-teacher-class-card-link {
        min-height: 6.8rem;
      }

      .w14-teacher-class-card .card-body {
        gap: 0.75rem;
        padding: 1rem;
      }

      .w14-teacher-class-icon {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.75rem;
        font-size: 1.4rem;
      }
    }
  </style>


  <!-- Style variation -->
  <h5 class="pb-1 mb-4">My Classes</h5>
  <h6 class="pb-1 mb-4 text-body-secondary">Active Classes</h6>
  <div class="row g-3 g-sm-4 mb-6 w14-teacher-class-grid">
    @foreach ($TeacherSubjectClass as $k => $subjectclass)
      @if (in_array($subjectclass->status, ['active', 'current'], true))
        @php
          $subjectDisplay = \App\Support\BookingSubjectProvisioning::displayPayloadForSubject(
              (int) ($subjectclass->subject_id ?? 0),
              $subjectclass->subject_name
          );
          $subjectName = (string) ($subjectDisplay['title'] ?? 'Subject');
          $subjectVisual = $subjectDisplay['visual'] ?? ['icon' => 'ti tabler-school', 'tone' => 'default'];
          $subjectIcon = (string) ($subjectVisual['icon'] ?? 'ti tabler-school');
          $subjectTone = (string) ($subjectVisual['tone'] ?? 'default');
          $activeStudents = $subjectclass->classSubject?->studentsSubjects
              ?->filter(function ($studentSubject) {
                  $student = $studentSubject->student;

                  return $student
                      && $studentSubject->status === 'active'
                      && in_array((string) ($student->account_status ?? 'active'), ['', 'active'], true);
              })
              ->pluck('student')
              ->unique('id')
              ->values() ?? collect();
          $teacherClassTitle = (string) $subjectclass->class_name;
          $teacherClassMeta = null;

          if ($activeStudents->count() === 1) {
              $student = $activeStudents->first();
              $studentName = trim(implode(' ', array_filter([$student->first_name, $student->last_name])));
              $teacherClassTitle = $studentName !== '' ? $studentName : ($student->student_email ?: $teacherClassTitle);
              $teacherClassMeta = (string) $subjectclass->class_name;
          } elseif ($activeStudents->count() > 1) {
              $teacherClassTitle = __(':count students', ['count' => $activeStudents->count()]);
              $teacherClassMeta = (string) $subjectclass->class_name;
          }
        @endphp

        <div class="col-12 col-md-6 col-xl-4">
          <div class="card mb-0 w14-teacher-class-card w14-subject-tone-{{ $subjectTone }}">
            <a href="{{ route('teacher.sessions', ['teachersubjectid' => $subjectclass->id]) }}" class="w14-teacher-class-card-link" aria-label="{{ __('Open :class :subject sessions', ['class' => $teacherClassTitle, 'subject' => $subjectName]) }}">
              <div class="card-body">
                <span class="w14-teacher-class-icon" aria-hidden="true">
                  <i class="{{ $subjectIcon }}"></i>
                </span>
                <div class="min-w-0">
                  <h5 class="w14-teacher-class-title">{{ $teacherClassTitle }}</h5>
                  @if ($teacherClassMeta && $teacherClassMeta !== $teacherClassTitle)
                    <span class="w14-teacher-class-meta">{{ $teacherClassMeta }}</span>
                  @endif
                  <span class="w14-teacher-class-subject">{{ $subjectName }}</span>
                </div>
              </div>
            </a>
          </div>
        </div>
      @endif
    @endforeach

  </div>

  <!--/ Card layout -->




@endsection
