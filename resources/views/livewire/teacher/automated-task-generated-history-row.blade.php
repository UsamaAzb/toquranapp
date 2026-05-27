@php
  $pivot = $task->taskStudents->first();
  $status = (string) ($pivot?->status ?? 'assigned');
  $statusTone = $status === 'completed' ? 'success' : ($status === 'assigned' ? 'warning' : 'secondary');
@endphp

<tr wire:key="history-task-{{ $task->id }}">
  <td style="min-width: 16rem;">
    <div class="fw-semibold">{{ $task->title }}</div>
    <div class="small text-muted">{{ $task->description ?: 'No description snapshot' }}</div>
  </td>
  <td>
    <span class="badge bg-label-primary rounded-pill">{{ $task->version_display_name_snapshot ?: 'No version snapshot' }}</span>
  </td>
  <td style="min-width: 14rem;">
    @if($task->attachments->isNotEmpty())
      <div class="d-flex flex-wrap gap-2">
        @foreach($task->attachments as $attachment)
          <x-sessions.attachment-chip
            wire:key="history-task-{{ $task->id }}-attachment-{{ $attachment->id }}"
            :attachment="[
              'id' => $attachment->id,
              'type' => $attachment->type,
              'name' => $attachment->title ?: 'Attachment',
              'path' => $attachment->path,
              'url' => $attachment->path,
            ]"
            :session-id="$session->id"
            role="teacher"
            :variant-index="$loop->index" />
        @endforeach
      </div>
    @else
      <span class="text-muted">No copied attachment</span>
    @endif
  </td>
  <td>
    <span class="badge bg-label-{{ $statusTone }} rounded-pill">{{ ucfirst($status) }}</span>
    @if($pivot?->submitted_at)
      <div class="small text-muted mt-1">Submitted {{ \Carbon\Carbon::parse($pivot->submitted_at)->format('Y-m-d H:i') }}</div>
    @endif
  </td>
</tr>
