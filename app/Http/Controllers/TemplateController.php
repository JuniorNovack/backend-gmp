<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaFile;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\TemplateStatusEnum;



class TemplateController extends Controller
{
    public function index()
    {
        try {
            $response = Template::where('created_by', Auth::id())
                ->with('baseTemplate')
                ->get();

            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function store(Request $request)
    {

        try {

            $params = $request->all();
            $validator = Validator::make($params, [
                'base_template_id' => 'nullable|exists:media_files,id',
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'duration' => 'required|integer|max:255',
            ]);

            if ($validator->fails()) {
                return  Response::error($validator->getMessageBag());
            }

            # custom_data that will contain all the values in the array 
            $custom_data = [
                "name" => $params['name'],
                'category' => $params['category'],
                'duration' => $params['duration'],
                'customizable' => TemplateStatusEnum::CUSTOMIZABLE_1,
                'status' => TemplateStatusEnum::CREATED,
            ];

            $response = Template::create([
                'base_template_id' => $params['base_template_id'],
                'name' => $params['name'],
                'category' => $params['category'],
                'duration' => $params['duration'],
                'customizable' => TemplateStatusEnum::CUSTOMIZABLE_1,
                'status' => TemplateStatusEnum::CREATED,
                'custom_data' => $custom_data,
                'created_by' => Auth::id(),
            ]);

            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function update(Request $request, Template $template)
    {

        try {
            $this->authorize('update', $template);

            $params = $request->all();
            $validator = Validator::make($params, [
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'duration' => 'required|integer|max:255',
            ]);

            if ($validator->fails()) {
                return  Response::error($validator->getMessageBag());
            }

            # custom_data that will contain all the values in the array 
            $custom_data = [
                "name" => $params['name'],
                'category' => $params['category'],
                'duration' => $params['duration'],
                'status' => TemplateStatusEnum::UPDATED,
            ];


            $response = $template->update([
                'name' => $params['name'],
                'category' => $params['category'],
                'duration' => $params['duration'],
                'status' => TemplateStatusEnum::UPDATED,
                'custom_data' => $custom_data
            ]);


            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function show(Template $template)
    {

        try {
            $this->authorize('view', $template);

            $response = Template::where('id', $template->id)
                ->with('baseTemplate')
                ->first();

            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }
    public function destroy(Template $template)
    {
        try {
            $this->authorize('delete', $template);

            $template->delete();

            return response()->json([
                'message' => 'Template deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }
}
