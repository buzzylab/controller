<?php

namespace MaherElGamil\Controller\Traits;

use Illuminate\Support\Str;

/**
 * Class ControllerTrait.
 */
trait ControllerTrait
{
    /**
     * @var null
     */
    protected $model = null;

    /**
     * @var null
     */
    protected $entity = null;

    /**
     * @var array
     */
    protected $singularExceptional = [
        'media',
    ];

    protected function getCalledClassNamespace()
    {
        return get_called_class();
    }

    protected function getCalledClassName()
    {
        $currentClassNameArray = explode('\\', get_called_class());

        return array_pop($currentClassNameArray);
    }

    protected function setModelNameSpace($namespace)
    {
        $this->modelNameSpace = $namespace;
    }

    protected function getModelNameSpace()
    {
        return $this->modelNameSpace ?? "App\\";
    }

    protected function getCalledModel()
    {
        return $this->getModelNameSpace().Str::singular(
            str_replace('Controller', '', $this->getCalledClassName())
        );
    }

    /**
     * @param $model
     *
     * @return $this
     */
    protected function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return $this
     */
    protected function getModel()
    {
        return $this->model ?? $this->getCalledModel();
    }

    /**
     * @param $package
     *
     * @return $this
     */
    protected function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getPackage()
    {
        return $this->package ?? null ;
    }

    /**
     * @return array
     */
    protected function getPackageNameParameters()
    {
        list($vendor, $name) = explode('/', $this->getPackage());

        return compact($vendor, $name);
    }

    /**
     * @return mixed
     */
    protected function getPackageVendorName()
    {
        return $this->getPackageNameParameters()['vendor'];
    }

    /**
     * @return mixed
     */
    protected function getPackageName()
    {
        return $this->getPackageNameParameters()['name'];
    }

    /**
     * @return string
     */
    protected function getSingularPackageName()
    {
        $packageName = $this->getPackageName();

        return in_array($packageName, $this->singularExceptional) ? $this->getEntity() : str_singular($packageName);
    }

    /**
     * @return mixed
     */
    protected function getDottedPackageName()
    {
        return str_replace('/', '.', $this->getPackage());
    }

    /**
     * @return mixed
     */
    protected function createModel()
    {
        return app($this->getModel());
    }

    protected function getCalledEntity()
    {
        return strtolower(
            Str::snake(str_replace('Controller', '', $this->getCalledClassName()))
        );
    }

    /**
     * Set entity.
     *
     * @param $entity
     *
     * @return $this
     */
    protected function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity.
     *
     * @return mixed
     */
    protected function getEntity()
    {
        return $this->entity ?? $this->getCalledEntity();
    }

    /**
     * @return mixed|string
     */
    protected function getSingularEntity()
    {
        return in_array($this->getEntity(), $this->singularExceptional) ? $this->getEntity() : str_singular($this->getEntity());
    }

    /**
     * @param array $requests
     *
     * @return $this
     */
    protected function setRequestValidation(array  $requests)
    {
        $this->requestsValidation = $requests;

        return $this;
    }

    /**
     * Get entity.
     *
     * @return mixed
     */
    protected function getRequestValidation()
    {
        return isset($this->requestsValidation) ? $this->requestsValidation : null;
    }

    /**
     * Set entity.
     *
     * @param $folder
     *
     * @return $this
     */
    protected function setNamespaceFolder($folder)
    {
        $this->viewsFolder = $folder;

        return $this;
    }

    protected function getCalledNamespaceFolder()
    {
        $currentClassNameArray = explode('\\', get_called_class());

        $first_folder = strtolower($currentClassNameArray[count($currentClassNameArray)-3]);
        $second_folder = strtolower($currentClassNameArray[count($currentClassNameArray)-2]);

        return "{$first_folder}.{$second_folder}";
    }

    /**
     * Get entity.
     *
     * @return mixed
     */
    protected function getNamespaceFolder()
    {
        return $this->viewsFolder ?? $this->getCalledNamespaceFolder();
    }

    /**
     * @param $action
     *
     * @return string
     */
    protected function getRouteNameByAction($action)
    {
        $dottedPackage = $this->getPackage() ? $this->getDottedPackageName().'.' : null ;

        return "{$dottedPackage}{$this->getNamespaceFolder()}.{$this->getEntity()}.{$action}";
    }

    /**
     * @param $viewName
     *
     * @return string
     */
    protected function getViewPath($viewName)
    {
        return (isset($this->package) ? $package = "{$this->getPackage()}::" : null).
            "{$this->getNamespaceFolder()}.{$this->getEntity()}.{$viewName}";
    }

    /**
     * @param $message
     *
     * @return string
     */
    protected function getMessagePhrase($message)
    {
        return trans(
            ($this->getPackage() ? "{$this->getPackage()}::" : null ).
            str_plural($this->getEntity())."/message.{$message}"
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get list of items
        ${$this->getEntity()} = $this->createModel()->get();

        if (request()->expectsJson()) {
            return ${$this->getEntity()};
        }

        // Show index page
        return view($this->getViewPath('index'), compact("{$this->getEntity()}"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return mixed
     */
    public function create()
    {
        return $this->showForm('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        return $this->processForm('create');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return $this->showForm('update', $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id)
    {
        return $this->processForm('update', $id);
    }

    /**
     * Shows the form.
     *
     * @param string $action
     * @param int    $id
     *
     * @return mixed
     */
    protected function showForm($action, $id = null)
    {
        if (! ${$this->getSingularEntity()} = $this->getOrNew($id)) {

            return redirect()
                ->route($this->getRouteNameByAction('all'))
                ->withErrors([$this->getMessagePhrase('not_found'), compact('id')]);
        }

        // Show the page
        return view($this->getViewPath('form'), compact('action', $this->getSingularEntity()));
    }

    /**
     * Processes the form.
     *
     * @param string $action
     * @param int    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function processForm($action, $id = null)
    {
        // Get request validation method
        $allRequestsValidation = $this->getRequestValidation();

        // Custom request || Laravel default request
        $request = isset($allRequestsValidation[$action]) ? app($allRequestsValidation[$action]) : request();

        // Lest's store the item
        if ($entityMessage = $this->updateOrCreate($action, $request, $id)) {
            if (request()->expectsJson()) {
                response([
                    'status' => 'success',
                    'data'   => $this->getMessagePhrase("message.success.{$action}"),
                ]);
            }

            return redirect()
                ->route($this->getRouteNameByAction('all'))
                ->withSuccess($this->getMessagePhrase("message.success.{$action}"));
        }

        if (request()->expectsJson()) {
            response([
                'status' => 'success',
                'data'   => $entityMessage,
            ]);
        }

        return redirect()->back()->withInput()->withErrors($entityMessage, 'form');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $item
     * @param  $type
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($item, $type = 'id')
    {
        // Delete item and get message
        $messageType = $this->createModel()->where($type, $item)->delete() ? 'Success' : 'Errors';

        // Get delete result boolean type
        $messageMethod = 'with'.$messageType;

        if (request()->expectsJson()) {
            response([
                'status' => 'success',
                'data'   => $this->getMessagePhrase('message.'.str_singular(strtolower($messageType)).'.delete'),
            ]);
        }

        return redirect()
            ->route($this->getRouteNameByAction('all'))
            ->{$messageMethod}($this->getMessagePhrase('message.'.str_singular(strtolower($messageType)).'.delete'));
    }

    /**
     * @param null   $item
     * @param string $type
     *
     * @return ControllerTrait
     */
    protected function getOrNew($item = null, $type = 'id')
    {
        return is_null($item) ?
            $this->createModel() :
            $this->createModel()->where($type, $item)->first();
    }

    /**
     * @param $action
     * @param $request
     * @param null $id
     *
     * @return mixed
     */
    protected function updateOrCreate($action, $request, $id = null)
    {
        $data = $request->except(['_method', '_token']);

        return is_null($id) ?
            $this->createModel()->{$action}($data) :
            $this->createModel()->where('id', $id)->{$action}($data);
    }
}
