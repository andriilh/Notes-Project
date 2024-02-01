<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Display all of notes data with filters
     * @param Http\Requset
     * @return Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /**
         * Method when() di sini berguna sebagai conditional method
         * Penggunaannya adalah when(jika_kondisi_terpenuhi, maka_lakukan)
         * Ini sama seperti:
         * if (jika_kondisi_terpenuhi) {
         *      maka_lakukan
         *  }
         */
        
         $notes = Note::when(
            $request->filled('search'),
            function ($query) use ($request) {
                return $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            }
        )->when(
            $request->filled('start_date') && $request->filled('end_date'),
            function ($query) use ($request) {
                $startDateTime = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay(); // convert ke bentuk datetime dari string dan dimulai awal hari 00:00:00
                $endDateTime = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay(); // diakhiri akhir hari 23:59:59

                return $query->whereBetween('created_at', [$startDateTime, $endDateTime]);
            }
        )->when(
            $request->filled('category'),
            function ($query) use ($request) {
                return $query->where('category_id', $request->category);
            }
        )->orderBy('created_at', 'desc')->paginate(10);


        return response()->json([
            'status' => 'success',
            $notes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Http\Requset
     * @return Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'         => ['required', 'max:255', 'string'],
            'content'       => ['required']
        ]);

        if ($validator->failed()) {
            return response()->json([
                'status'    => 'error',
                'error'     => $validator->messages()
            ], 400);
        }

        Note::create([
            'title'         => $request->title,
            'content'       => $request->content,
            'category_id'      => $request->category
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Succesfully add new notes'
        ]);
    }

    /**
     * Display the specified resource.
     * @param id
     * @return Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $note = Note::findOrFail($id);
            return response()->json([
                'status'    => 'success',
                'note'      => $note
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => "Couldn't find any note"
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Http\Requset & id
     * @return Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'         => ['required', 'max:255', 'string'],
            'content'       => ['required']
        ]);

        if ($validator->failed()) {
            return response()->json([
                'status'    => 'error',
                'error'     => $validator->messages()
            ], 400);
        }

        try {
            $note = Note::findOrFail($id);
            $note->update([
                'title'         => $request->title,
                'content'       => $request->content,
                'category_id'   => $request->category
            ]);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Succesfully update notes'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => "Couldn't that any note"
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param id
     * @return Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $note = Note::findOrFail($id);
            $note->delete();
            return response()->json([
                'status'    => 'success',
                'message'   => 'Successfully deleted the note'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => "Couldn't find any note"
            ], 400);
        }
    }
}
