<?php

namespace App\Http\Controllers;

use App\Services\CentralpayPaymentService;
use App\Settings;
use Illuminate\Http\Request;
use App\Pack;
use App\User_Pack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminPackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show page only if user is admin
        if (Auth::user()->role == 'admin') {
            $on_page = 'Pachete';
            $packages = Pack::where('custom', '!=', 1)->orderBy('created_at', 'desc')->get();
            return view('admin.packages', compact('on_page', 'packages'));
        } else {
            return redirect('/admin/users');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ( Auth::user()->role == 'admin' ) {
            $on_page = 'Editare Pachete';
        if ( $id == 'add' ) {
            $pack = '';
        } else {
            $pack = Pack::where('id', $id)->firstOrFail();
        }
        return view('admin.edit_packages', compact('on_page', 'pack'));
        } else {
            return redirect('/admin/users');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ( Auth::user()->role == 'admin' ) {
            if ( $id == 'add' ) {
                // Add new package
                $requests = $request->all();
                $pack = new Pack;
                foreach($requests as $key => $value) {
                    if ( $key != '_token' ) {
                        $pack->{$key} = $request->{$key};
                    }
                }
            } else {
                // Update current package, by id
                $requests = $request->all();
                $pack = Pack::where('id', $id)->firstOrFail();

                foreach($requests as $key => $value)
                {
                    if( $key!= '_token' ) {
                        $pack->{$key} = $request->{$key};
                    }
                }
            }

            if ( $request->type == "subscription" ) {
                // Call CentralPay service and update the package
                $CP_client_id = Settings::where('name', 'CENTRALPAY_CLIENT_ID')->first();
                $CP_publickey = Settings::where('name', 'CENTRALPAY_PUBLICKEY')->first();
                $CP_secret = Settings::where('name', 'CENTRALPAY_SECRET')->first();

                $centralPayService = new CentralpayPaymentService($CP_client_id->value, $CP_secret->value, $CP_publickey->value);

                if (!empty($pack->centralpay_model_id)) {
                    $centralPayService->setSubscriptionModelId($pack->centralpay_model_id);
                }

                $centralPayModelID = $centralPayService->updateOrCreateSubscriptionModel(
                    $pack->name,
                    $pack->price,
                    'DAY',
                    $pack->duration,
                    $pack->id
                );

                if (empty($pack->centralpay_model_id)) {
                    $pack->centralpay_model_id = $centralPayModelID;
                }
            }

            // An empty select/number field arrives as '' — the column is a nullable tinyint.
            if ($pack->video_blur_percent === '') {
                $pack->video_blur_percent = null;
            }

            // Save the package
            $pack->save();

            return redirect('/admin/packages');
        } else {
            return redirect('/admin/users');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if( Auth::user()->role == 'admin' ) {
            $pack = Pack::where('id', $id)->firstOrFail();
            $pack->delete();
            $pk = User_Pack::where('pack_id', $id);
            $pk->delete();
            return redirect('/admin/packages');
        } else {
            return redirect('/admin/users');
        }
    }
}
