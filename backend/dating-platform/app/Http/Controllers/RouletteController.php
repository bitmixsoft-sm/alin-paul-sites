<?php

namespace App\Http\Controllers;

use App\Discount;
use App\Pack;
use App\RouletteUser;
use App\RouletteValue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RouletteController extends Controller
{

    public function add_value(Request $request)
    {

        if (Auth::check() && !Auth::user()->isAdmin()) {

            return redirect()->route('admin_dash');
        }

        //TODO: Verificare parametrii obligatorii
        $package = Pack::where('id', $request->pack_id)->first();

        $rouletteValue = new RouletteValue();
        $rouletteValue->display_text = $package->name . ' ' . $request->value . '%';
        $rouletteValue->pack_id = $request->pack_id;
        $rouletteValue->value = $request->value;
        $rouletteValue->save();

        return redirect()->route('admin_roulette_values');
    }

    public function delete_value($id)
    {

        if (Auth::check() && !Auth::user()->isAdmin()) {

            return redirect()->route('admin_dash');
        }

        //TODO: Verificare parametrii obligatorii
        $rouletteValue = RouletteValue::where('id', $id);

        if ($rouletteValue->exists()) {

            $rouletteValue->delete();
            return redirect()->route('admin_roulette_values');
        } else {

            //TODO:
        }
    }

    public function index(Request $request)
    {
        $title = l('Roulette');

        //return view('roulette', compact('title'));
        return redirect()->route('index');
    }

    public function random_value()
    {
        $userId = Auth::id();

        if (!(config('app.debug') || !Auth::user()->hasDiscount())) {
            return redirect()->route('roulette');
        }

        $lastSpin = new RouletteUser();
        $lastSpin->timestamp = time();
        $lastSpin->user_id = $userId;
        $lastSpin->save();

        $rouletteValues = RouletteValue::get();
        $randomRouletteValueIndex = rand(0, sizeof($rouletteValues) - 1);
        $randomRouletteValueId = $rouletteValues[$randomRouletteValueIndex]->id;

        //Salvează valoarea în baza de date ca fiind câștigătoare
        $RouletteValue = RouletteValue::where('id', $randomRouletteValueId)->first();

        $discount = new Discount();
        $discount->user_id = $userId;
        $discount->pack_id = $RouletteValue->pack_id;
        $discount->value = $RouletteValue->value;
        $discount->ending_at = Carbon::now()->addHours(5);
        $discount->save();

        return response()->json(array('value' => $randomRouletteValueIndex));
    }

    public function values(Request $request)
    {

        $title = l('Roulette values');

        if (Auth::check() && !Auth::user()->isAdmin()) {

            return redirect()->route('admin_dash');
        }

        $on_page = 'Reduceri';

        $rouletteValues = RouletteValue::all();
        $discounts = [];

        return view('admin.roulette_values', compact('rouletteValues', 'on_page', 'discounts'));
    }
}