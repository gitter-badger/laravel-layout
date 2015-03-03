<?php namespace Layout;

use Illuminate\Contracts\Events\Dispatcher;
use Layout\Exceptions\InvalidRouterNameException;

class Factory
{
    const PROFILER_KEY   = 'dispatch::route';

    /**
     * Blocks registry.
     *
     * @var array
     */
    protected $_blocks = [];

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new view factory instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Retrieve current layout object.
     *
     * @return \Layout\Layout
     */
    public function getLayout()
    {
        return app('render.layout');
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Illuminate\View\View
     */
    public function render($handles = null, $generateBlocks = true, $generateXml = true)
    {
        $this->loadLayout($handles, $generateBlocks, $generateXml);

        $view = $this->renderLayout();

        return view('render::template.page.root', ['html' => $view]);
    }

    /**
     * Load layout by handles(s).
     *
     * @param string|null|bool $handles
     * @param bool             $generateBlocks
     * @param bool             $generateXml
     *
     * @return Layout\Factory
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        // if handles were specified in arguments load them first
        if (false !== $handles && '' !== $handles) {
            $this->getLayout()->getUpdate()->addHandle($handles ? $handles : 'default');
        }
        // add default layout handles for this action
        $this->addRouteLayoutHandles();
        $this->operatingSystemHandle();
        $this->browserHandle();
        $this->loadLayoutUpdates();
        if (!$generateXml) {
            return $this;
        }
        $this->generateLayoutXml();
        if (!$generateBlocks) {
            return $this;
        }
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        return $this;
    }

    public function addRouteLayoutHandles()
    {
        $update = $this->getLayout()->getUpdate();
        // load action handle
        $update->addHandle($this->routeHandler());

        return $this;
    }

    public function loadLayoutUpdates()
    {
        $_profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();
        // dispatch event for adding handles to layout update
        $this->events->fire(
            'route.layout.load.before',
            ['route' => $this, 'layout' => $this->getLayout()]
        );
        // load layout updates by specified handles
        start_profile("$_profilerKey::layout_load");
        $this->getLayout()->getUpdate()->load();
        stop_profile("$_profilerKey::layout_load");

        return $this;
    }

    public function generateLayoutXml()
    {
        $_profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();

        $this->events->fire(
            'route.layout.generate.xml.before',
            ['route' => $this, 'layout' => $this->getLayout()]
        );

        // generate xml from collected text updates
        start_profile("$_profilerKey::layout_generate_xml");
        $this->getLayout()->generateXml();
        stop_profile("$_profilerKey::layout_generate_xml");

        return $this;
    }

    public function generateLayoutBlocks()
    {
        $_profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();
        // dispatch event for adding xml layout elements

        $this->events->fire(
            'route.layout.generate.blocks.before',
            ['route' => $this, 'layout' => $this->getLayout()]
        );

        // generate blocks from xml layout
        start_profile("$_profilerKey::layout_generate_blocks");
        $this->getLayout()->generateBlocks();
        stop_profile("$_profilerKey::layout_generate_blocks");

        $this->events->fire(
            'route.layout.generate.blocks.after',
            ['route' => $this, 'layout' => $this->getLayout()]
        );

        return $this;
    }

    /**
     * Rendering layout.
     *
     * @param string $output
     */
    public function renderLayout($output = '')
    {
        $_profilerKey = self::PROFILER_KEY.'::'.$this->routeHandler();

        start_profile("$_profilerKey::layout_render");
        if ('' !== $output) {
            $this->getLayout()->addOutputBlock($output);
        }

        $this->events->fire('route.layout.render.before');
        $this->events->fire('route.layout.render.before.'.$this->routeHandler());

        $this->getLayout()->setDirectOutput(false);

        $output = $this->getLayout()->getOutput();

        stop_profile("$_profilerKey::layout_render");

        return $output;
    }

    protected function routeHandler()
    {
        $route_name = \Route::currentRouteName();

        if (empty($route_name)) {
            throw new InvalidRouterNameException('Invalid Router Name supplied');
        }

        return str_replace('.', '_', strtolower($route_name));
    }

    /**
     * Add a handle for operating systems, e.g.:
     * <layout>
     *   <operating_system_linux>
     *   </operating_system_linux>
     * </layout>.
     *
     * @return Layout\Factory
     */
    public function operatingSystemHandle()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/Linux/', $agent)) {
            $os = 'linux';
        } elseif (preg_match('/Win/', $agent)) {
            $os = 'windows';
        } elseif (preg_match('/Mac/', $agent)) {
            $os = 'osx';
        } else {
            $os = null;
        }
        if ($os) {
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('operating_system_'.$os);
        }

        return $this;
    }
    /**
     * Add layout handle for browser type, e.g.:
     * <layout>
     *   <browser_firefox>
     *   </browser_firefox>
     * </layout>.
     *
     * @return Layout\Factory
     */
    public function browserHandle()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (stripos($agent, 'Firefox') !== false) {
            $agent = 'firefox';
        } elseif (stripos($agent, 'MSIE') !== false) {
            $agent = 'ie';
        } elseif (stripos($agent, 'iPad') !== false) {
            $agent = 'ipad';
        } elseif (stripos($agent, 'Android') !== false) {
            $agent = 'android';
        } elseif (stripos($agent, 'Chrome') !== false) {
            $agent = 'chrome';
        } elseif (stripos($agent, 'Safari') !== false) {
            $agent = 'safari';
        } else {
            $agent = null;
        }
        if ($agent) {
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('browser_'.$agent);
        }

        return $this;
    }
}
