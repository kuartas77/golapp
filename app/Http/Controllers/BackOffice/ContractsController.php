<?php

namespace App\Http\Controllers\BackOffice;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Http\Controllers\Controller;

class ContractsController extends Controller
{
    public function index()
    {
        $contracts = Contract::with(['school', 'contract_type'])->get();
        return view("backoffice.contracts.index", compact('contracts'));
    }

    public function create()
    {
        $contract = new Contract();
        return view('backoffice.contracts.create', compact('contract'));
    }

    public function store(Request $request)
    {

        // preg_match_all("/\[.*\]/", $request->body, $parameters);
        // $parameters = array_values(array_unique(Arr::collapse($parameters)));
        $parameters = $this->getParameters($request->only(['header', 'body', 'footer']));

        $contract = new Contract();
        $contract->name = $request->input('name');
        $contract->school_id = $request->input('school_id');
        $contract->header = $request->input('header');
        $contract->body = $request->input('body');
        $contract->footer = $request->input('footer');
        $contract->parameters = join(',', $parameters);
        $contract->save();

        return redirect(route('config.contracts.index'));
    }

    public function edit(Contract $contract)
    {
        return view('backoffice.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        $parameters = $this->getParameters($request->only(['header', 'body', 'footer']));
        $contract->name = $request->input('name');
        $contract->school_id = $request->input('school_id');
        $contract->header = $request->input('header');
        $contract->body = $request->input('body');
        $contract->footer = $request->input('footer');
        $contract->parameters = join(',', $parameters);
        $contract->save();

        return redirect(route('config.contracts.index'));
    }

    public function show(Contract $contract)
    {

    }

    private function getParameters(array $fields)
    {
        $step = [];
        foreach ($fields as $field) {
            preg_match_all('/\\[[^\\]]*\\]/i', $field, $step);
            $parameters[] = Arr::collapse($step);
        }
        return array_values(array_unique(Arr::collapse($parameters)));
    }
}
