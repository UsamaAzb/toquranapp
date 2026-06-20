<div>
<div class="background-container">

    <div class="weekly-progress card shadow-sm">
        <div class="card-body d-flex align-items-center">
            <i class="bi bi-calendar-week me-2"></i>
            <div>
                <strong>This week</strong><br>
                <small>0/{{ count($session['tasks'] ?? []) }} islands done</small>
            </div>
        </div>
    </div>

    <div class="topic-track d-flex flex-lg-row flex-column align-items-center justify-content-center">
        @foreach ($session['tasks'] as $index => $topic)
            <div class="topic-block d-flex align-items-center position-relative">

              <div class="up-next">UP NEXT!</div>
              <div class="up-next-line"></div>

                <div class="topic-circle text-center"
                     x-data
                     x-on:click="Livewire.dispatch('open-task', { taskId: {{ $topic['id'] }} })">
                    <span class="badge bg-warning text-dark">New topic</span>
                    <h6>{{ $topic['title'] }}</h6>
                </div>
                @if (!$loop->last)
                        <div class="topic-line topic-line-vertical d-lg-none"></div>
                    @endif

                    @if (!$loop->last)
                        <div class="topic-line topic-line-horizontal d-none d-lg-block"></div>
                    @endif
            </div>
        @endforeach
    </div>

</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Background */
.background-container {
    background-color: #f8f4e8;
    min-height: 100vh;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    overflow-x: auto;
}

/* Weekly progress box */
.weekly-progress {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 220px;
    background-color: #ffffffee;
    border-radius: 12px;
    padding: 4px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 10;
    font-size: 0.9rem;
    font-weight: 600;
}

/* Topic timeline container */
.topic-track {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    /* padding: 2rem; */
}

/* Topic circle */
.topic-circle {
    width: 180px;
    height: 180px;
    background: linear-gradient(145deg, #ffffff, #f1f1f1);
    border-radius: 50%;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    padding: 10px;
    text-align: center;
}

.topic-circle:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 18px rgba(0,0,0,0.2);
    border: 3px solid #cc0000;
}

.topic-circle h6 {
    margin-top: 0.8rem;
    font-weight: 700;
    font-size: 1rem;
    color: #333;
}

/* New topic badge */
.topic-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #b50606;
    color: white;
    padding: 4px 10px;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Line between topics */
.topic-line {
    width: 60px;
    height: 6px;
    background-color: white;
    margin: 0 0px;
    flex-shrink: 0;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}


/* UP NEXT label and arrow */
.up-next {
    position: absolute;
    top: -59px;
    left: 43%;
    background: #732323;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 5px;
    white-space: nowrap;
    z-index: 5;
}


.up-next-line {
    width: 4px;
    height: 56px;
    background-color: #000;
    position: absolute;
    top: -56px;
    left: 99px;
    z-index: 4;
}

/* Mobile responsive */
@media (max-width: 992px) {
    .topic-track {
        flex-direction: column;
        align-items: center;
        margin-top: 150px;

    }
    .weekly-progress{
      right: 30% !important;

    }
.topic-block{
      flex-direction: column !important;
}
    .topic-circle {
        /* margin: 30px 0; */
        z-index: 2;
    }

    .topic-line {
        width: 4px;
        height: 40px;
        background-color: white;
    }

    .up-next {
      top: -50px;
  left: 71%;
  transform: translateX(-50%);
    }

    .up-next-line {
      width: 4px;
  height: 46px;
  top: -46px;
  left: 50%;
  transform: translateX(-50%);
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openTopic(topicId) {
    // Replace this with route or modal logic
    alert("Open Topic ID: " + topicId);
    // Example: window.location.href = "/topics/" + topicId;
}
</script>
</div>
