<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificationMail;
use App\Mail\WelcomeMail;
use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\GraphQL\Exceptions\ExceptionHandler;
use App\Repositories\Eloquents\UserRepository;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->authorizeResource(User::class,'user');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */

    public function index(Request $request)
    {
        try {

            $users = $this->filter($this->repository, $request);
            return $users->latest('created_at')->paginate($request->paginate ?? $users->count());

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserRequest $request)
    {
        $createdUser = $this->repository->store($request);







        $clearedPassword = $request->input('password');
        $roleName = Helpers::getRoleNameByUserId($createdUser->id);

        if ($roleName == RoleEnum::CONSUMER  || $roleName == RoleEnum::ADMIN) {
            // Ajouter l'utilisateur dans TracCar
            if ($createdUser){
                $result = Helpers::traccar_call('api/users',[
                    'name' => $createdUser->name,
                    'password'=>  $request->input('password'),
                    'email' => $createdUser->email,
                    'phone' => (string) $createdUser->phone,
                    'deviceLimit' => -1
                ],'post');
                if($result ){
                    $createdUser->traccar_user_id = $result->id;
                    $createdUser->update();
                }


             }

        }

        return $createdUser;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->repository->show($user->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->repository->update($request->all(), $user->getId($request));
        // Mettre à jour l'utilisateur dans TracCar
        if ($updatedUser){
            $roleName = Helpers::getRoleNameByUserId($updatedUser->id);
            if ($roleName == RoleEnum::CONSUMER  || $roleName == RoleEnum::ADMIN) {
                Helpers::traccar_call('api/users/'.$updatedUser->traccar_user_id,[
                    'id'=>$updatedUser->traccar_user_id,
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'phone' => (string) $updatedUser->phone,
                ],'PUT');
            }
        }

        return $updatedUser;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        $response = $this->repository->destroy($user->getId($request));
        if ($response){
            $roleName = Helpers::getRoleNameByUserId($user->getId($request));
             Log::info($roleName);
            if ($roleName == RoleEnum::CONSUMER || $roleName == RoleEnum::ADMIN ) {
                Helpers::traccar_call('api/users/'.$user->traccar_user_id,null,'DELETE');
            }
        }
       return  $response;
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    public function status($id, $status)
    {
        return $this->repository->status($id, $status);
    }

    public function deleteAddress(Request $request, User $user)
    {
        return $this->repository->deleteAddress($user->getId($request));
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function import()
    {
        return $this->repository->import();
    }

    public function getUsersExportUrl(Request $request)
    {
        return $this->repository->getUsersExportUrl($request);
    }

    public function export()
    {
        return $this->repository->export();
    }

    public function filter($users, $request)
    {
        if (Helpers::isUserLogin()) {
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName != RoleEnum::ADMIN) {
                $users = $users->where('created_by_id',Helpers::getCurrentUserId());
            }
        }

        if ($request->field && $request->sort) {
            $users = $users->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $users = $users->where('status',$request->status);
        }

        if ($request->isStoreExists) {
            $users = $users->whereIn('id', function ($query) {
                $query->select('vendor_id')->from('stores')->get();
            });

            if (!filter_var($request->isStoreExists, FILTER_VALIDATE_BOOLEAN)) {
                $users = $users->whereNotIn('id', function ($query) {
                    $query->select('vendor_id')->from('stores')->get();
                });
            }
        }

        if ($request->role) {
            $role = $request->role;
            $users = $users->whereHas("roles", function($query) use($role) {
                $query->whereName($role);
            });

        } else {

            $users = $users->whereHas("roles", function($query){
                $query->whereNotIn("name", [RoleEnum::ADMIN, RoleEnum::VENDOR]);
            });
        }

        return $users;
    }

    public function savePlayerId(Request $request)
    {
        $user = User::find($request->input('userId')); // Trouver l'utilisateur par son ID
        if ($user) {
            $user->player_id = $request->input('playerId'); // Enregistrer le PlayerID
            $user->save();

            return response()->json(['message' => 'PlayerID enregistré avec succès'], 200);
        }

        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    }
}
