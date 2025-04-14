<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Requests\ServiceRequest;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Resources\Service as ServiceResource;

class ServiceController extends ResponseController
{
    //C.R.u.D.
    public function getServices()
    {
        $services = Service::all(); // Az összes szolgáltatás lekérése
    
        // Válasz formázása
        $formattedServices = $services->map(function ($service) {
            return [
                'name'  => $service->service, // A szolgáltatás neve
                'value' => $service->id,      // A szolgáltatás id-ja
                'price' => $service->price,   // Az ár hozzáadása
            ];
        });
    
        return response()->json([
            'success' => true,
            'data'    => $formattedServices
        ]);
    }
    

    public function getService( Request $request ){
        $service = Service::where( "service", $request[ "service "])->first();
        if( is_null( $service )){
            return $this->sendError( "Adathiba.", [ "Nincs ilyen szolgálatás." ], 406);
        }else{
            return $this->sendResponse( $service, "Szolgáltatás listázva." );
        }
    }

    public function addService(Request $request) {
        $request->validate([
            'service' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);
    
        $service = Service::create([
            'service' => $request->service,
            'price' => $request->price,
        ]);
    
        return $this->sendResponse(new ServiceResource($service), "Új szolgáltatás rögzítve.");
    }

    public function updateService(Request $request, $id) {
        $service = Service::find($id);
        if (!$service) {
          return response()->json(['message' => 'Szolgáltatás nem található'], 404);
        }
      
        $service->update($request->all());
        return response()->json(['message' => 'Szolgáltatás frissítve', 'data' => $service]);
      }
    
    

    public function deleteService($id)
    {
        $service = Service::find($id);
        if ($service) {
            $service->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Service not found'], 404);
    }
    

    public function getServiceId( $serviceName ){
        $service = Service::where( "service", $serviceName )->first();
        $id = $service->id;

        return $id;
    }

}
