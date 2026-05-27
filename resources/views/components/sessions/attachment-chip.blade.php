@props([
  'attachment' => [],
  'sessionId' => null,
  'dailySessionId' => null,
  'templateId' => null,
  'differentiatedTaskId' => null,
  'taskId' => null,
  'role' => 'teacher',
  'studentId' => null,
  'variantIndex' => null,
])

@php
  $type = strtolower((string) ($attachment['type'] ?? 'file'));
  $path = (string) ($attachment['path'] ?? '');
  $pathForExt = parse_url($path, PHP_URL_PATH) ?: $path;
  $ext = strtolower(pathinfo($pathForExt, PATHINFO_EXTENSION));
  $name = (string) ($attachment['name'] ?? ($path ? basename($pathForExt) : 'Attachment'));
  $url = (string) ($attachment['url'] ?? $path);
  $attachmentId = $attachment['id'] ?? null;

  $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
  $isVocabularyGame = $type === 'vocabulary_game'
    || str_contains($path, '/vocabulary/games/assignment/');
  $isFileAttachment = ! in_array($type, ['link', 'youtube', 'vocabulary_game'], true) && ! $isVocabularyGame;
  $isImage = $isFileAttachment && in_array($ext, $imageExts, true);

  $iconClass = match (true) {
    $type === 'youtube' => 'ti tabler-brand-youtube',
    $isVocabularyGame => 'ti tabler-balloon',
    $type === 'link' => 'ti tabler-link',
    in_array($ext, $imageExts, true) => 'ti tabler-photo',
    $ext === 'pdf' => 'ti tabler-file-type-pdf',
    in_array($ext, ['doc', 'docx'], true) => 'ti tabler-file-type-doc',
    in_array($ext, ['xls', 'xlsx', 'csv'], true) => 'ti tabler-file-type-xls',
    in_array($ext, ['ppt', 'pptx'], true) => 'ti tabler-file-type-ppt',
    in_array($ext, ['zip', 'rar', '7z'], true) => 'ti tabler-file-zip',
    in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'ogv'], true) => 'ti tabler-video',
    in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'], true) => 'ti tabler-music',
    default => 'ti tabler-file-description',
  };

  $semanticToneClass = match ($type) {
    'youtube' => 'session-attachment-semantic-youtube',
    'vocabulary_game' => 'session-attachment-semantic-link',
    'link' => 'session-attachment-semantic-link',
    default => '',
  };

  $thumbIndex = is_numeric($variantIndex)
    ? ((int) $variantIndex % 6)
    : (abs(crc32($name)) % 6);
  $thumbClass = 'session-attachment-thumb-' . $thumbIndex;

  $classes = 'text-decoration-none session-attachment-chip';
  $canOpenStudyViewer = in_array($role, ['student', 'teacher', 'parent'], true)
    && $sessionId
    && $taskId
    && $attachmentId
    && ! $dailySessionId
    && ! $templateId
    && ! $differentiatedTaskId;

  $attachmentShowUrl = '';
  $attachmentContentUrl = $url;

  if ($dailySessionId) {
    $attachmentShowUrl = route('daily-sessions.attachment.show', [
      'dailySession' => $dailySessionId,
      'attachment' => $attachmentId,
    ]);
    $attachmentContentUrl = $isFileAttachment
      ? route('daily-sessions.attachment.file', [
        'dailySession' => $dailySessionId,
        'attachment' => $attachmentId,
      ])
      : $url;
  } elseif ($templateId) {
    $attachmentShowUrl = route('daily-sessions.template-attachment.show', [
      'template' => $templateId,
      'attachment' => $attachmentId,
    ]);
    $attachmentContentUrl = $isFileAttachment
      ? route('daily-sessions.template-attachment.file', [
        'template' => $templateId,
        'attachment' => $attachmentId,
      ])
      : $url;
  } elseif ($differentiatedTaskId) {
    $attachmentShowUrl = route('differentiated-tasks.attachment.show', [
      'task' => $differentiatedTaskId,
      'attachment' => $attachmentId,
    ]);
    $attachmentContentUrl = $isFileAttachment
      ? route('differentiated-tasks.attachment.file', [
        'task' => $differentiatedTaskId,
        'attachment' => $attachmentId,
      ])
      : $url;
  } elseif ($sessionId) {
    $attachmentShowUrl = $role === 'student'
      ? route('student.sessions.attachment.show', [
        'session' => $sessionId,
        'attachment' => $attachmentId,
        'student_id' => $studentId,
      ])
      : route('teacher.sessions.attachment.show', [
        'session' => $sessionId,
        'attachment' => $attachmentId,
      ]);
    $attachmentContentUrl = $isFileAttachment
      ? ($role === 'student'
        ? route('student.sessions.attachment.file', [
          'session' => $sessionId,
          'attachment' => $attachmentId,
          'student_id' => $studentId,
        ])
        : route('teacher.sessions.attachment.file', [
          'session' => $sessionId,
          'attachment' => $attachmentId,
        ]))
      : $url;
  } else {
    $attachmentShowUrl = $url;
  }

  $linkHref = $url;
  $linkOpensNewTab = true;

  if (($type === 'link' || $isVocabularyGame) && $url !== '') {
    $isSameOriginLink = \Illuminate\Support\Str::startsWith($url, [url('/'), '/']);

    if ($isSameOriginLink) {
      $returnTarget = request()->is('livewire/*')
        ? (request()->headers->get('referer') ?: url('/'))
        : url()->full();
      $returnTarget = app(\App\Services\Library\ResourceReturnTargetResolver::class)
        ->safeSameOriginUrl($returnTarget) ?? url('/');

      if (! str_contains($url, 'return_to=')) {
        $separator = str_contains($url, '?') ? '&' : '?';
        $linkHref = $url . $separator . http_build_query(['return_to' => $returnTarget]);
      }

      $linkOpensNewTab = false;
    }
  }
@endphp

@if($canOpenStudyViewer)
  <button
    type="button"
    class="{{ $classes }} session-attachment-chip-button"
    title="{{ $name }}"
    wire:click="openAttachmentStudyViewer({{ (int) $sessionId }}, {{ (int) $taskId }}, {{ (int) $attachmentId }})"
    onclick="event.stopPropagation();">
    <span class="session-attachment-icon {{ $thumbClass }} {{ $semanticToneClass }}" aria-hidden="true">
      <i class="{{ $iconClass }}"></i>
    </span>
    <span class="session-attachment-name">{{ $name }}</span>
  </button>
@elseif($type === 'link' || $isVocabularyGame)
  <a href="{{ $linkHref }}"
     class="{{ $classes }}"
     title="{{ $name }}"
     @if($linkOpensNewTab) target="_blank" rel="noopener noreferrer" @endif
     data-session-return-anchor="true"
     onclick="event.stopPropagation();">
    <span class="session-attachment-icon {{ $thumbClass }} {{ $semanticToneClass }}" aria-hidden="true">
      <i class="{{ $iconClass }}"></i>
    </span>
    <span class="session-attachment-name">{{ $name }}</span>
  </a>
@elseif($isImage && ! $differentiatedTaskId)
  <a href="#"
     class="{{ $classes }} image-attachment"
     title="{{ $name }}"
     data-bs-toggle="modal"
     data-bs-target="#imageAttachmentModal"
     data-img-src="{{ $attachmentContentUrl }}"
     data-img-title="{{ $name }}"
     onclick="event.preventDefault(); event.stopPropagation();">
    <span class="session-attachment-icon {{ $thumbClass }} {{ $semanticToneClass }}" aria-hidden="true">
      <i class="{{ $iconClass }}"></i>
    </span>
    <span class="session-attachment-name">{{ $name }}</span>
  </a>
@else
  <a href="{{ $attachmentShowUrl }}"
     class="{{ $classes }}"
     title="{{ $name }}"
     data-session-return-anchor="true"
     onclick="event.stopPropagation();">
    <span class="session-attachment-icon {{ $thumbClass }} {{ $semanticToneClass }}" aria-hidden="true">
      <i class="{{ $iconClass }}"></i>
    </span>
    <span class="session-attachment-name">{{ $name }}</span>
  </a>
@endif
