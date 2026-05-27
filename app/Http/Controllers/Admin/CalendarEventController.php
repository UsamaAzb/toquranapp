<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CalendarEvent;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarEventController extends Controller
{
    public function view()
    {
        $booking_users = Booking::where('status', 'confirmed')->get();
        $student_users = Student::where('status', 'active')->get();

        return view('admin.calendar.app-calendar', compact('booking_users', 'student_users')); // عدّل المسار حسب مشروعك
    }

    // قراءة الأحداث (مع فلاتر التاريخ والأنواع المختارة)
    // public function index(Request $request)
    // {
    //   $request->validate([
    //     'start' => 'required|date',
    //     'end'   => 'required|date',
    //     'calendars' => 'array'
    //   ]);
    //
    //   $start = Carbon::parse($request->start);
    //   $end   = Carbon::parse($request->end);
    //   $cats  = $request->input('calendars', []); // ['busy','booking',..]
    //
    //   $q = CalendarEvent::query();
    //
    //   if (!empty($cats)) {
    //     $q->whereIn('category', $cats);
    //   }
    //
    //   // أي حدث يتقاطع مع المدى [start, end]
    //   $q->where(function ($qq) use ($start, $end) {
    //     $qq->whereBetween('start', [$start, $end])
    //        ->orWhereBetween('end',   [$start, $end])
    //        ->orWhere(function ($qq2) use ($start, $end) {
    //           $qq2->where('start', '<=', $start)
    //               ->where('end',   '>=', $end);
    //        });
    //   });
    //
    //   $events = $q->get()->map(function ($e) {
    //     return [
    //       'id'    => $e->id,
    //       'title' => $e->title,
    //       'start' => optional($e->start)->toIso8601String(),
    //       'end'   => optional($e->end)->toIso8601String(),
    //       'allDay'=> $e->all_day,
    //       'url'   => $e->url,
    //       'extendedProps' => [
    //         'calendar'   => ucfirst($e->category),
    //         'guests'     => $e->guests,
    //         'description'=> $e->description,
    //       ],
    //     ];
    //   });
    //   return response()->json($events);
    // }

    public function index(Request $request)
    {
        // 1) التحقق من المدخلات (يشمل عناصر المصفوفة)

        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'calendars' => 'array',
            'calendars.*' => 'in:busy,available,consultation,classes,holiday',
        ]);

        $start = \Carbon\Carbon::parse($request->start);
        $end = \Carbon\Carbon::parse($request->end);
        $cats = $request->input('calendars', []); // ['busy','available',...]

        $q = CalendarEvent::query();

        // 2) فلترة النوع (لو وصل شيء)
        if (! empty($cats)) {
            $q->whereIn('category', $cats); // category مخزنة lowercase
        }

        // 3) شرط "تداخل المدى" robust (يغطي end = NULL)
        // الحدث يتقاطع مع [start, end) إذا:
        // start < end_range  &&  (end IS NULL || end > start_range)
        $q->where('start', '<', $end)
            ->where(function ($qq) use ($start) {
                $qq->whereNull('end')
                    ->orWhere('end', '>', $start);
            });

        // (اختياري) ترتيب
        $events = $q->orderBy('start', 'asc')
            ->get()
            ->map(function ($e) {
                $url = $e->url;
                if (is_string($url)) {
                    $t = trim($url);
                    if ($t === '' || strcasecmp($t, 'null') === 0 || strcasecmp($t, 'undefined') === 0) {
                        $url = null;
                    }
                }

                return [
                    'id' => $e->id,
                    'title' => $e->title,
                    'start' => optional($e->start)->toIso8601String(),
                    'end' => optional($e->end)->toIso8601String(),
                    'allDay' => (bool) $e->all_day,
                    'url' => $url,
                    'extendedProps' => [
                        'calendar' => $e->category,     // <-- lowercase ثابت
                        'guests' => $e->guests,       // رقم أو null
                        'description' => $e->description,
                    ],
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        if (Auth::check()) {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'start' => 'required|date',
                'end' => 'nullable|date',
                'allDay' => 'nullable|boolean',
                'url' => 'nullable|url',
                // 'location'    => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'calendar' => 'required|in:busy,available,consultation,classes,holiday',
                'guests' => 'nullable|integer',
            ]);

            $allDay = (bool) ($data['allDay'] ?? false);
            $start = Carbon::parse($data['start']);
            $end = isset($data['end']) ? Carbon::parse($data['end']) : null;

            // في All-day: نجعل end حصرية بإضافة يوم
            if ($allDay && $end) {
                $end = $end->copy()->addDay();
            }

            $event = CalendarEvent::create([
                'title' => $data['title'],
                'start' => $start,
                'end' => $end,
                'all_day' => $allDay,
                'url' => $data['url'] ?? null,
                // 'location'   => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'category' => $data['calendar'],
                'guests' => $data['guests'] ?? null,
                'created_by_user_id' => auth()->id(),
                // 'created_by_user_id' => 1,
            ]);

            return response()->json(['message' => 'Created', 'id' => $event->id], 201);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function update(Request $request, CalendarEvent $event)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'allDay' => 'nullable|boolean',
            'url' => 'nullable|url',
            // 'location'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'calendar' => 'required|in:busy,available,consultation,classes,holiday',
            'guests' => 'nullable|integer',
        ]);

        $allDay = (bool) ($data['allDay'] ?? false);
        $start = Carbon::parse($data['start']);
        $end = isset($data['end']) ? Carbon::parse($data['end']) : null;
        if ($allDay && $end) {
            $end = $end->copy()->addDay();
        }

        $event->update([
            'title' => $data['title'],
            'start' => $start,
            'end' => $end,
            'all_day' => $allDay,
            'url' => $data['url'] ?? null,
            // 'location'   => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'category' => $data['calendar'],
            'guests' => $data['guests'] ?? null,
        ]);

        return response()->json(['message' => 'Updated']);
    }

    public function destroy(CalendarEvent $event)
    {
        $event->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function guests(Request $request)
    {
        $label = strtolower($request->query('label', ''));

        // سنبني مصفوفة على شكل Select2: { results: [ {id,text}, ... ] }
        $results = collect();

        if ($label === 'classes') {
            // طلاب Active، مرتّبين بالاسم الأول ثم الأخير
            $items = Student::where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name']);

            $results = $items->map(function ($s) {
                $full = trim(($s->first_name ?? '').' '.($s->last_name ?? ''));

                return [
                    'id' => $s->id,
                    'text' => $full !== '' ? $full : ('Student #'.$s->id),
                ];
            });

        } elseif ($label === 'consultation') {
            // حجوزات Confirmed — نعرض الاسم إن وجد، وإلا نعرض رقم الحجز
            $items = Booking::where('status', 'confirmed')
                ->orderBy('id')
                ->get(['id', 'parent_name']);

            $results = $items->map(function ($b) {
                $name = $b->parent_name ?? null;

                return [
                    'id' => $b->id,                       // نخزن رقم الحجز كـ integer واحد
                    'text' => $name !== null && $name !== '' ? $name : ('Booking #'.$b->id),
                ];
            });
        }

        // Select2 تتوقع هيكل { results: [...] }
        return response()->json(['results' => $results->values()]);
    }
}
