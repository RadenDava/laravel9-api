<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return void
     */
    public function index(){
        $posts = Post::latest()->paginate(5);
        return new PostResource(true, 'List Data Posts',$posts);
    }

     /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request){
        
        //ketentuan validator
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //if validasi salah
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload img
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //return respon
        return new PostResource(true, 'Data Ditambahkan', $post);

        
    }

    /**
         * show
         * 
         * @param mixed $post
         * @return void
         */
        public function show(Post $post){
            return new PostResource(true, 'Data Ditemukan', $post);
        }

        /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $post
     * @return void
     */
    public function update(Request $request, Post $post)
    {
        //ketentuan validator
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        //if validator salah
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload img baru
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //hapus gambar lama
            Storage::delete('public/posts/'.$post->image);

            //update pke gambar baru
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);

        } else {

            //update tanpa gambar
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return respon
        return new PostResource(true, 'Data di UPDATE', $post);
    }

    /**
     * Hapus
     * 
     * @param mixed $post
     * @return void
     */
    public function destroy(Post $post){

        //hapus img
        Storage::delete('public/posts/'.$post->image);

        //hapus post
        $post->delete();

        //return respon
        return new PostResource(true,'Data HAS been ELIMINATED', null);
    }
}
