@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
<script defer src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

@section('title', 'reword system')
<style>
.progress-pill .progress-bar {
  background-size: 20px 20px;
  /* Subtle diagonal stripes overlay */
  background-image:
    linear-gradient(180deg,#ffcf5a 0%, #f7a325 100%),
    repeating-linear-gradient(
      45deg,
      rgba(255,255,255,.18) 0,
      rgba(255,255,255,.18) 10px,
      rgba(0,0,0,.05) 10px,
      rgba(0,0,0,.05) 20px
    );
}

</style>
@section('content')
<livewire:ui.points-progress
    :student-id="$student_id"
    :teacher-subject-id="$teachersubjectid"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="true"
    label="Reward Points"
/>
<div id="confetti-overlay" style="position:fixed; left:0; top:0; width:100vw; height:100vh; pointer-events:none; z-index:2000;">
  <canvas id="confetti-canvas" style="width:100vw; height:100vh; display:block;"></canvas>
</div>
<script>
(function () {
  var MODAL_ID = 'pinModal';         // غيّرها لو ID المودال مختلف
  var CANVAS_ID = 'confetti-canvas';
  var OVERLAY_ID = 'confetti-overlay';
  var Z_BEHIND_MODAL = 1052;         // خلف المودال (وفوق الـ backdrop)
  var Z_OVER_MODAL   = 2000;         // فوق المودال

  var _confetti = null;
  var _canvas   = null;

  function resizeCanvasToViewport() {
    if (!_canvas) return;
    // اضبط البافر الداخلي وفقًا لـ DPR — مهم جدًا للتموضع الصحيح
    var dpr = Math.max(1, window.devicePixelRatio || 1);
    var w = Math.max(1, Math.floor(window.innerWidth  * dpr));
    var h = Math.max(1, Math.floor(window.innerHeight * dpr));
    // عيّن أبعاد البافر الداخلي
    if (_canvas.width !== w)  _canvas.width  = w;
    if (_canvas.height !== h) _canvas.height = h;
    // العرض/الارتفاع بالـ CSS ثابتين على 100vw/100vh من الـ style
  }

  function getConfetti() {
    if (!_canvas) _canvas = document.getElementById(CANVAS_ID);
    if (!_canvas || !window.confetti) return null;
    if (!_confetti) {
      // بدون worker لتفادي Offscreen/resize conflict
      _confetti = window.confetti.create(_canvas, { resize: false, useWorker: false });
      resizeCanvasToViewport();
      // حافظ على الحجم مع تغيّر النافذة/الدوران
      window.addEventListener('resize', resizeCanvasToViewport);
      window.addEventListener('orientationchange', resizeCanvasToViewport);
    }
    return _confetti;
  }

  function setConfettiLayer(where) {
    var overlay = document.getElementById(OVERLAY_ID);
    if (!overlay) return;
    overlay.style.zIndex = (where === 'behind') ? Z_BEHIND_MODAL : Z_OVER_MODAL;
  }

  function playClaimConfetti() {
    var c = getConfetti();
    if (!c) return;
    c({
      particleCount: 140,     // كان 220 → عدد أقل = أسرع في الاختفاء
      spread: 70,             // انتشار أهدى
      startVelocity: 40,      // سرعة مبدئية معقولة
      ticks: 120,             // كان 320 → عدد إطارات أقل = مدة أقصر
      gravity: 1.25,          // كان 0.8 → سقوط أسرع
      decay: 0.86,            // كان 0.9 → يتباطأ أسرع وينتهي بدري
      scalar: 1.1,            // حجم مناسب
      origin: { x: 0.5, y: 0.5 },
      colors: ['#ff1e56', '#ffd400', '#00c853', '#2962ff', '#7c4dff']
    });
  }

  // افوكس تلقائي على خانة PIN لما المودال يظهر (اختياري)
  function focusPinWhenShown() {
    var el = document.getElementById(MODAL_ID);
    if (!el) return;
    el.addEventListener('shown.bs.modal', function onShown() {
      el.removeEventListener('shown.bs.modal', onShown);
      var input = el.querySelector('#pinInput');
      if (input) { input.focus(); input.select && input.select(); }
    }, { once: true });
  }

  document.addEventListener('livewire:initialized', function () {
    window.addEventListener('pin-modal:open', function () {
      setConfettiLayer('behind'); // اختياري: أثناء إدخال الـ PIN
      focusPinWhenShown();
      var el = document.getElementById(MODAL_ID);
      if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    window.addEventListener('pin-modal:close', function () {
      setConfettiLayer('over');
      var el = document.getElementById(MODAL_ID);
      if (!el) return;
      (bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el)).hide();
    });

    // تشغيل الاحتفالية بعد ما المودال يقفل تمامًا
    window.addEventListener('redeem:success', function () {
      var el = document.getElementById(MODAL_ID);
      if (el && el.classList.contains('show')) {
        el.addEventListener('hidden.bs.modal', function onHidden() {
          el.removeEventListener('hidden.bs.modal', onHidden);
          setConfettiLayer('over');
          // اعمل resize مباشرة قبل الرمي لضمان المنتصف الحقيقي
          resizeCanvasToViewport();
          // انتظر فريمتين لثبات المقاسات بعد الإغلاق
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              playClaimConfetti();
            });
          });
        }, { once: true });
        (bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el)).hide();
      } else {
        setConfettiLayer('over');
        resizeCanvasToViewport();
        playClaimConfetti();
      }
    });
  });
})();
</script>

@endsection
