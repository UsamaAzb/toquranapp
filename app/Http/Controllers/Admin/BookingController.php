<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingIntakeDetectionService;
use App\Services\BookingIntakeWriter;
use App\Support\BookingIntakePayloadRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function storeIntake(
        Request $request,
        BookingIntakeDetectionService $bookingIntakeDetectionService,
        BookingIntakeWriter $bookingIntakeWriter
    ): JsonResponse {
        $input = $request->all();
        $validator = Validator::make(
            $input,
            BookingIntakePayloadRules::rules(),
            BookingIntakePayloadRules::messages(),
            BookingIntakePayloadRules::attributes()
        );
        BookingIntakePayloadRules::applyAfter($validator, $input);

        $payload = $validator->validate();

        $result = $bookingIntakeDetectionService->withSubmissionFingerprintLock(
            $payload,
            function () use ($payload, $bookingIntakeDetectionService, $bookingIntakeWriter): array {
                $detection = $bookingIntakeDetectionService->analyze($payload);

                if (($detection['route'] ?? null) === 'review') {
                    $review = $bookingIntakeDetectionService->writeReviewRecord($payload, $detection);

                    return [
                        'route' => 'review',
                        'review_id' => $review->id,
                    ];
                }

                $booking = $bookingIntakeWriter->createFromDetectionPayload($payload, $detection);

                return [
                    'route' => 'normal',
                    'booking_id' => $booking->id,
                    'child_ids' => $booking->children->pluck('id')->all(),
                ];
            }
        );

        if ($result['route'] === 'review') {
            return response()->json([
                'route' => 'review',
                'review_id' => $result['review_id'],
                'message' => 'Submission routed to the intake review queue.',
            ], 202);
        }

        return response()->json([
            'route' => 'normal',
            'booking_id' => $result['booking_id'],
            'child_ids' => $result['child_ids'],
            'message' => 'Booking intake saved to the normal queue.',
        ], 201);
    }
}
