<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\NotificationRepository;

class NotificationController extends Controller
{
    protected $repository;

    public function __construct(NotificationRepository $repository){
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $user = $this->repository->findOrFail(Helpers::getCurrentUserId());
        return $user->notifications()->latest('created_at')->paginate($request->paginate ?? $user->count());
    }

    public function markAsRead(Request $request)
    {
        return $this->repository->markAsRead($request);
    }

    public function changeParams(Request $request,$device_id)
    {
        $device = Device::with('vehicle.notifs')->where('traccar_device_id',$device_id)->first();
        $device->vehicle->notifs()->syncWithoutDetaching([
            $request->input('notif') => ['alert' => $request->input('checked'),'method' => implode(',',$request->input('selectedMethodes'))]
        ]);
         return ['success'=> true];
    }

    public function destroy(Request $request)
    {
        return $this->repository->destroy($request->id);
    }
}
