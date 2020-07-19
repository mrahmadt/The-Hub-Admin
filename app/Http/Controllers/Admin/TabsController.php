<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tab\BulkDestroyTab;
use App\Http\Requests\Admin\Tab\DestroyTab;
use App\Http\Requests\Admin\Tab\IndexTab;
use App\Http\Requests\Admin\Tab\StoreTab;
use App\Http\Requests\Admin\Tab\UpdateTab;
use App\Models\Tab;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Category;
use App\Models\Tabtype;

use Illuminate\Http\Request;

class TabsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param IndexTab $request
     * @return array|Factory|View
     */
    public function index(IndexTab $request)
    {
        // create and AdminListing instance for a specific model and
        $data = AdminListing::create(Tab::class)->processRequestAndGet(
            // pass the request with params
            $request,

            // set columns to query
            ['id', 'name', 'tabtype_id', 'category_id','activated', 'itemorder'],

            // set columns to searchIn
            ['name'],

            function ($query) use ($request) {
                $query->orderBy('itemorder', 'asc')->orderBy('updated_at', 'desc');
                $query->with(['category','tabtype']);
                if($request->has('categories')){
                    $query->whereIn('category_id', $request->get('categories'));
                }
                if($request->has('tabtypes')){
                    $query->whereIn('tabtype_id', $request->get('tabtypes'));
                }
            }
        );

        if ($request->ajax()) {
            if ($request->has('bulk')) {
                return [
                    'bulkItems' => $data->pluck('id')
                ];
            }
            return ['data' => $data];
        }

        return view('admin.tab.index', [
        'data' => $data
        , 'categories' => Category::all(), 'tabtypes' => Tabtype::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function create(Request  $request)
    {
        $this->authorize('admin.tab.create');

        $tabtype_id = $request->input('t',1);
        $tabtype = Tabtype::find($tabtype_id);
        
        if(!$tabtype) return redirect('admin/tabs');

        $tab = new Tab;
        $tab->tabtype_id = $tabtype_id;
        $tab->activated = 1;

        $TabTypes = Tabtype::all();

        
        return view('admin.tab.create', [
            'categories' => Category::all(),
            'tabtypes' => $TabTypes,
            'tabtype' => $tabtype,
            'tab' => $tab,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTab $request
     * @return array|RedirectResponse|Redirector
     */
    public function store(StoreTab $request)
    {

        if (!$request->has('tabtype_id')){
            return redirect('admin/tabs');
        }
        
        $tabtype = Tabtype::find($request->tabtype_id);
        if(!$tabtype) return redirect('admin/tabs');
           
        // Sanitize input
        $sanitized = $request->getSanitized($tabtype);
        
        // Store the Tab
        $tab = Tab::create($sanitized);
        DB::statement('call sortTabs(?,?)', [$tab->id,1]);

        if ($request->ajax()) {
            return ['redirect' => url('admin/tabs'), 'message' => trans('brackets/admin-ui::admin.operation.succeeded')];
        }

        return redirect('admin/tabs');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Tab $tab
     * @throws AuthorizationException
     * @return Factory|View
     */
    public function edit(Tab $tab)
    {
        $this->authorize('admin.tab.edit', $tab);

        $tabtype = Tabtype::find($tab->tabtype_id);
        if(!$tabtype) return redirect('admin/tabs');
        
        return view('admin.tab.edit', [
            'tab' => $tab,
            'categories' => Category::all(),
            'tabtypes' => Tabtype::all(),
            'tabtype' => $tabtype,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTab $request
     * @param Tab $tab
     * @return array|RedirectResponse|Redirector
     */
    public function update(UpdateTab $request, Tab $tab)
    {
        // Sanitize input
        $tabtype = Tabtype::find($tab->tabtype_id);
        if(!$tabtype) return redirect('admin/tabs');
           
        // Sanitize input
        $sanitized = $request->getSanitized($tabtype);

        //$sanitized['category_id'] = $request->getCategoryId();

        // Update changed values Tab
        $tab->update($sanitized);

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/tabs'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }

        return redirect('admin/tabs');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyTab $request
     * @param Tab $tab
     * @throws Exception
     * @return ResponseFactory|RedirectResponse|Response
     */
    public function destroy(DestroyTab $request, Tab $tab)
    {
        $tab->delete();

        if ($request->ajax()) {
            return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param BulkDestroyTab $request
     * @throws Exception
     * @return Response|bool
     */
    public function bulkDestroy(BulkDestroyTab $request) : Response
    {
        DB::transaction(static function () use ($request) {
            collect($request->data['ids'])
                ->chunk(1000)
                ->each(static function ($bulkChunk) {
                    Tab::whereIn('id', $bulkChunk)->delete();

                    // TODO your code goes here
                });
        });

        return response(['message' => trans('brackets/admin-ui::admin.operation.succeeded')]);
    }

    
    public function itemup(Request $request, Tab $tab)
    {
        if($tab->itemorder!=1){
            DB::statement('call sortTabs(?,?)', [$tab->id, $tab->itemorder-1]);
        }

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/tabs'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }
        return redirect()->intended('admin/tabs');
    }

    public function itemdown(Request $request, Tab $tab)
    {
        if($tab->itemorder<=65500){ //65535
            DB::select('call sortTabs(?,?)', [$tab->id, $tab->itemorder+1]);
        }

        if ($request->ajax()) {
            return [
                'redirect' => url('admin/tabs'),
                'message' => trans('brackets/admin-ui::admin.operation.succeeded'),
            ];
        }
        return redirect()->intended('admin/tabs');
    }

}
