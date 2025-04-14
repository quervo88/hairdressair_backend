<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\Employee as EmployeeResource;

class EmployeeController extends ResponseController
{
    // Dolgozók listázása
    public function getEmployees()
{
    $employees = Employee::select('id', 'name')->get();

    return response()->json([
        'success' => true,
        'data' => $employees
    ]);
}


    // Egy dolgozó lekérdezése ID alapján
    public function getEmployee(Request $request)
    {
        $employee = Employee::where("user_id", $request["user_id"])->first();
        
        if (!$employee) {
            return $this->sendError("Adathiba.", ["Nincs ilyen dolgozó."], 406);
        }

        return $this->sendResponse(new EmployeeResource($employee), "Dolgozó listázva.");
    }

    // Új dolgozó hozzáadása felhasználóból
    public function addEmployee(Request $request)
    {
        $user = User::find($request["user_id"]);

        if (!$user) {
            return $this->sendError("Adathiba.", ["Nincs ilyen felhasználó."], 404);
        }

        $employee = new Employee([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $request["phone"] ?? null
        ]);

        $employee->save();

        return $this->sendResponse(new EmployeeResource($employee), "Új dolgozó rögzítve.");
    }

    // Dolgozó adatainak frissítése
    public function updateEmployee(EmployeeRequest $request)
    {
        $request->validated();
        
        $employee = Employee::find($request["id"]);
        
        if (!$employee) {
            return $this->sendError("Adathiba.", ["Nincs ilyen dolgozó."], 406);
        }

        $employee->name = $request["name"] ?? $employee->name;
        $employee->email = $request["email"] ?? $employee->email;
        $employee->phone = $request["phone"] ?? $employee->phone;
        $employee->update();

        return $this->sendResponse(new EmployeeResource($employee), "Dolgozó adatai módosítva.");
    }

    // Dolgozó törlése
    public function deleteEmployee(Request $request)
    {
        $employee = Employee::find($request["id"]);

        if (!$employee) {
            return $this->sendError("Adathiba.", ["Nincs ilyen dolgozó."], 406);
        }

        $employee->delete();
        return $this->sendResponse([], "Dolgozó törölve.");
    }

    // Egy dolgozó ID-jának lekérdezése név alapján
    public function getEmployeeId($employeeName)
    {
        $employee = Employee::where("name", $employeeName)->first();

        if (!$employee) {
            return $this->sendError("Adathiba.", ["Nincs ilyen dolgozó."], 406);
        }

        return $this->sendResponse(["id" => $employee->id], "Dolgozó ID lekérve.");
    }

    // Dolgozó hozzáadása user_id alapján
    public function addEmployeeByUserId(Request $request, $userId)
    {
        // Ellenőrizzük, hogy a felhasználó létezik-e
        $user = User::find($userId);
        
        if (!$user) {
            return $this->sendError("Adathiba.", ["Nincs ilyen felhasználó."], 404);
        }

        // Ellenőrizzük, hogy már létezik-e az employee táblában
        if (Employee::where('user_id', $userId)->exists()) {
            return $this->sendError("Ez a felhasználó már dolgozó.", [], 409);
        }

        // Új dolgozó létrehozása
        $employee = new Employee([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $request["phone"] ?? null
        ]);

        $employee->save();

        return $this->sendResponse(new EmployeeResource($employee), "Felhasználó dolgozóként rögzítve.");
    }

    public function removeEmployeeByUserId($userId)
    {
    // Ellenőrizzük, hogy a felhasználó létezik-e az employees táblában
    $employee = Employee::where('user_id', $userId)->first();

    if (!$employee) {
        return $this->sendError("Adathiba.", ["Ez a felhasználó nem dolgozó."], 404);
    }

    // Töröljük az alkalmazottat
    $employee->delete();

    return $this->sendResponse([], "A felhasználó dolgozóként történő eltávolítása sikeres.");
    }

}
