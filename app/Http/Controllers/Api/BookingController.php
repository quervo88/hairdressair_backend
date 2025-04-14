<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Requests\BookingRequest;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Resources\Booking as BookingResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class BookingController extends ResponseController
{
    //C.R.U.D.
    // public function getBookings(){

    //     $bookings = Booking::with( "customer", "employee", "service" )->get();

    //     return $this->sendResponse( BookingResource::collection( $bookings ), "Adatok betöltve.");
    // }

    public function getBookings()
    {
        // Ellenőrizzük, hogy a felhasználó admin 1-e
        $user = auth()->user();
        
        // Ha a felhasználó admin 1, akkor a saját foglalásait nézheti meg
        if ($user->admin == 1) {
            // Lekérjük a felhasználóhoz tartozó alkalmazotti id-t
            $employeeId = $user->employee->id;
    
            // Lekérjük a foglalásokat az alkalmazott id alapján
            $bookings = Booking::with('user', 'employee', 'service')
                ->where('employee_id', $employeeId)
                ->get()
                ->map(function ($booking) {
                    return [
                        'booking_id' => $booking->id,
                        'customer_name' => $booking->user->name,
                        'employee_name' => $booking->employee->name,
                        'service_name' => $booking->service->service,
                        'appointment_date' => $booking->appointment_date,
                        'appointment_time' => $booking->appointment_time
                    ];
                });
    
            return $this->sendResponse($bookings, "Adatok betöltve.");
        }
    
        // Ha a felhasználó admin 0, akkor csak a saját foglalásait láthatja
        if ($user->admin == 0) {
            // Lekérjük a felhasználó saját foglalásait a user_id alapján
            $bookings = Booking::with('user', 'employee', 'service')
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($booking) {
                    return [
                        'booking_id' => $booking->id,
                        'customer_name' => $booking->user->name,
                        'employee_name' => $booking->employee->name,
                        'service_name' => $booking->service->service,
                        'appointment_date' => $booking->appointment_date,
                        'appointment_time' => $booking->appointment_time
                    ];
                });
    
            return $this->sendResponse($bookings, "Adatok betöltve.");
        }
    
        // Ha nem admin, vagy admin 2 (superadmin), akkor az összes foglalást megjelenítjük
        $bookings = Booking::with('user', 'employee', 'service')->get()
            ->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->user->name,
                    'employee_name' => $booking->employee->name,
                    'service_name' => $booking->service->service,
                    'appointment_date' => $booking->appointment_date,
                    'appointment_time' => $booking->appointment_time
                ];
            });
    
        return $this->sendResponse($bookings, "Adatok betöltve.");
    }
    
    
    

    public function getOneBooking( Request $request ){

        $booking = Booking::where( "booking", $request[ "booking" ])->first();

        if( !$booking){
            return $this->sendError( "Adathiba.", [ "Nincs ilyen foglalás." ], 400 );
        }

        return $this->sendResponse( new BookingResource( $booking ), "Foglalás betöltve." );
    }

    public function addBooking( BookingRequest $request ){
        
        Gate::before( function(){
            $user = auth( "sanctum" )->user();
            if( $user->admin == 2 ){
                return true;
            }
        });
        if( !Gate::allows( "admin" )){
            return $this->sendError( "Autentikációs hiba.", [ "Nincs jogosultsága." ], 401 );
        }

        $request->validated();

        $booking = new Booking;
        $booking -> booking = $request[ "booking" ];
        //$booking-> datetime = $request[ "" ];
        $booking -> customer_id = ( new CustomerController )->getCustomerId( $request[ "customer" ]);
        $booking -> employee_id = ( new EmployeeController )->getEmployeeId( $request[ "employee" ]);
        $booking -> service_id = ( new ServiceController )->getServiceId( $request[ "service" ]);

        $booking->save();

        return $this->sendResponse( new BookingResource( $booking ), "A foglalás rögzítve." );
    }

    public function updateBooking( BookingRequest $request ){

        Gate::before( function(){

            $user = auth( "sanctum" )->user();
            if( $user->admin == 2 ){

                return true;
            }
        });
        if ( !Gate::allows( "admin" )) {

            return $this->sendError( "Autentikációs hiba.", [ "Nincs jogosultsága." ], 401 );
        }

        $request->validated();

        $booking = Booking::find( $request[ "id" ]);
        if( is_null( $booking )){
            return $this->sendError( "Adathiba.", [ "Nincs ilyen foglalás." ], 400 );
        }else{
            $booking->booking = $request[ "booking" ];
            //$booking-> datetime = $request[ "" ];
            $booking -> customer_id = ( new CustomerController )->getCustomerId( $request[ "customer" ]);
            $booking -> employee_id = ( new EmployeeController )->getEmployeeId( $request[ "employee" ]);
            $booking -> service_id = ( new ServiceController )->getServiceId( $request[ "service" ]);

            $booking->update();

            //return $this->sendResponse( new BookingResource( $booking ), "A foglalás módosítva." ); SZIT.HU verzio?
            return $this->sendResponse( $booking, "A foglalás módosítva." );
        }
    }

    public function deleteBooking(Request $request, $id)
    {
        $booking = Booking::find($id);
    
        if ($booking) {
            $booking->delete();
            return $this->sendResponse($booking, "A foglalás törölve.");
        } else {
            return $this->sendError("Nincs ilyen foglalás.");
        }
    }
    

    public function store(Request $request)
{
    $request->merge([
        'appointment_time' => date('H:i:s', strtotime($request->appointment_time))
    ]);
    
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'employee_id' => 'required|exists:employees,id',
        'service_id' => 'required|exists:services,id',
        'appointment_date' => 'required|date',
        'appointment_time' => 'required'
    ]);

    // Ellenőrzés: van-e már ilyen foglalás?
    $alreadyBooked = Booking::where('employee_id', $request->employee_id)
        ->where('appointment_date', $request->appointment_date)
        ->where('appointment_time', $request->appointment_time)
        ->exists();

    if ($alreadyBooked) {
        return response()->json(['message' => 'Ez az időpont már foglalt ennél a fodrásznál!'], 400);
    }

    // Ha nincs ütközés, mentjük a foglalást
    $booking = Booking::create([
        'user_id' => $request->user_id,
        'employee_id' => $request->employee_id,
        'service_id' => $request->service_id,
        'appointment_date' => $request->appointment_date,
        'appointment_time' => $request->appointment_time
    ]);

    return response()->json(['message' => 'Foglalás sikeres!', 'booking' => $booking], 201);
}

public function getBookedTimes($stylistId, $date)
{
    $bookings = DB::table('bookings')
    ->where('employee_id', $stylistId)
    ->where('appointment_date', $date)
    ->pluck('appointment_time')
    ->map(function ($time) {
        return date('H:i', strtotime($time));
    });

return response()->json($bookings);
}

public function cancelBooking(Request $request)
{
    // Validáljuk, hogy a kérés tartalmazza a foglalás ID-ját
    $request->validate([
        'booking_id' => 'required|exists:bookings,id',
    ]);

    // Megkeressük a foglalást
    $booking = Booking::find($request->booking_id);

    // Ellenőrizzük, hogy létezik-e a foglalás
    if (!$booking) {
        return response()->json(['message' => 'Foglalás nem található!'], 404);
    }

    // Töröljük a foglalást
    $booking->delete();

    return response()->json(['message' => 'Foglalás sikeresen törölve!'], 200);
}

public function getCalendarBookedAppointments($employee_id, $date)
{
    dd($date);  // Debug üzenet, hogy lássuk, mit kap a backend
    $bookedTimes = Booking::where('employee_id', $employee_id)
        ->whereDate('appointment_date', $date)
        ->get(['appointment_time', 'service_name', 'user_name']); 

    // A válasz formázása
    $events = $bookedTimes->map(function ($booking) use ($date) {
        return [
            'title' => $booking->user_name . ' - ' . $booking->service_name,
            'start' => $date . 'T' . $booking->appointment_time, 
            'end' => $date . 'T' . $booking->appointment_time,
        ];
    });

    return response()->json($events);
}




}
