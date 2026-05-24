<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\HostelDetailResource;
use App\Http\Resources\HostelResource;
use App\Services\HostelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelOwnerController extends Controller
{
    public function __construct(private readonly HostelService $hostels) {}

    public function index(Request $request): JsonResponse
    {
        $filters   = $request->only(['status']);
        $paginator = $this->hostels->getOwnerDashboard($request->user()->id, $filters);
        return ApiResponse::paginated(HostelResource::collection($paginator), $paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hostel_type_id' => 'required|exists:hostel_types,id',
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'gender_policy'  => 'nullable|in:male,female,mixed',
            'price'          => 'required|integer|min:0',
            'price_unit'     => 'nullable|in:seat/month,day',
            'advance'        => 'nullable|integer|min:0',
            'meal_included'  => 'nullable|boolean',
            'meal_price'     => 'nullable|integer|min:0',
            'curfew_time'    => 'nullable|string|max:20',
            'floor'          => 'nullable|string|max:100',
            'building'       => 'nullable|string|max:100',
            'rules'          => 'nullable|array',
            'rules.*'        => 'string|max:500',
            'owner_name'     => 'nullable|string|max:255',
            'owner_phone'    => 'nullable|string|max:20',
            'amenities'      => 'nullable|array',
            'amenities.*'    => 'integer|exists:amenities,id',
        ]);

        $hostel = $this->hostels->create($request->user()->id, $data);
        return ApiResponse::success(new HostelResource($hostel), 'Hostel draft created.', 201);
    }

    public function uploadPhotos(Request $request, string $id): JsonResponse
    {
        $request->validate(['photos' => 'required|array', 'photos.*' => 'image|max:5120']);
        $this->hostels->addPhotos($id, $request->user()->id, $request->file('photos'));
        return ApiResponse::success(['hostel_step' => 2]);
    }

    public function updateLocation(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'address'           => 'nullable|string|max:500',
            'landmark'          => 'nullable|string|max:255',
            'landmark_distance' => 'nullable|string|max:100',
            'division_id'       => 'nullable|exists:divisions,id',
            'district_id'       => 'nullable|exists:districts,id',
            'upazila_id'        => 'nullable|exists:upazilas,id',
            'union_id'          => 'nullable|exists:unions,id',
            'coord_x'           => 'nullable|numeric',
            'coord_y'           => 'nullable|numeric',
        ]);

        $hostel = $this->hostels->updateLocation($id, $request->user()->id, $data);
        return ApiResponse::success(new HostelResource($hostel));
    }

    public function addRoom(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'seats'              => 'nullable|array',
            'seats.*.seat_number'=> 'required|string|max:20',
            'seats.*.status'     => 'nullable|in:vacant,taken,reserved',
        ]);

        $room = $this->hostels->addRoom($id, $request->user()->id, $data);

        return ApiResponse::success([
            'id'    => $room->id,
            'name'  => $room->name,
            'seats' => $room->seats->map(fn ($s) => ['id' => $s->id, 'seat_number' => $s->seat_number, 'status' => $s->status]),
        ], 'Room added.', 201);
    }

    public function updateRoom(Request $request, string $id, string $rid): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $room = $this->hostels->updateRoom($id, $request->user()->id, $rid, $data);
        return ApiResponse::success(['id' => $room->id, 'name' => $room->name]);
    }

    public function updateSeat(Request $request, string $id, string $sid): JsonResponse
    {
        $data = $request->validate([
            'status'      => 'required|in:vacant,taken,reserved',
            'seat_number' => 'nullable|string|max:20',
        ]);

        $seat = $this->hostels->updateSeat($id, $request->user()->id, $sid, $data);
        return ApiResponse::success(['id' => $seat->id, 'seat_number' => $seat->seat_number, 'status' => $seat->status]);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $hostel = $this->hostels->submit($id, $request->user()->id);
        return ApiResponse::success(new HostelResource($hostel), 'Hostel submitted for review.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'gender_policy' => 'nullable|in:male,female,mixed',
            'price'         => 'sometimes|integer|min:0',
            'price_unit'    => 'nullable|in:seat/month,day',
            'advance'       => 'nullable|integer|min:0',
            'meal_included' => 'nullable|boolean',
            'meal_price'    => 'nullable|integer|min:0',
            'curfew_time'   => 'nullable|string|max:20',
            'floor'         => 'nullable|string|max:100',
            'building'      => 'nullable|string|max:100',
            'rules'         => 'nullable|array',
            'rules.*'       => 'string|max:500',
            'owner_name'    => 'nullable|string|max:255',
            'owner_phone'   => 'nullable|string|max:20',
            'amenities'     => 'nullable|array',
            'amenities.*'   => 'integer|exists:amenities,id',
        ]);

        $hostel = $this->hostels->update($id, $request->user()->id, $data);
        return ApiResponse::success(new HostelDetailResource($hostel));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->hostels->delete($id, $request->user()->id);
        return ApiResponse::success(null, 'Hostel deleted.');
    }
}
