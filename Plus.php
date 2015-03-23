<?php
/**
 * Plus - a PHP 5 Asynchronous I/O framework
 * 
 * @package     Plus
 * @copyright   2014 (c) Mohammed Al Ashaal
 * @author      Mohammed Al Ashaal <http://is.gd/alash3al>
 * @link        http://alash3al.github.io/Plus
 * @license     MIT LICENSE
 * @version     1.0.0
 * 
 * LGPL LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice must be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Plus;

/**
 * Prototype
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class Prototype extends \ArrayObject
{
    /**
     * The instances storage of this class
     * @var ArrayObject
     */
    protected   static   $instances  =   null;

    /**
     * Constructor
     * 
     * @param   mixed   $from   the input to be imported
     */
    public function __construct($from = array())
    {
        parent::__construct($from);

        if ( ! self::$instances instanceof \ArrayObject ) {
            self::$instances    =   new \ArrayObject(array());
        }

        self::$instances[$c = get_called_class()] = $this;
    }

    /** Destructor */
    public function __destruct()
    {
        self::$instances    =   null;
    }

    /**
     * Called to normalize the key
     * 
     * @param   string  $key    the key to be normalized
     * @return  string
     */
    public function __key($key)
    {
        return $key;
    }

    /**
     * Get a key from the storage
     * 
     * @param   string  $key    the key to fetch
     * @return  mixed
     */
    public function __get($key)
    {
        if ( ! isset($this[$key]) ) {
            $this[$key] = new Prototype;
        } elseif ( is_array($this[$key]) ) {
            $this[$key] = new Prototype($this[$key]);
        }

        return $this[$key];
    }

    /**
     * Store a new key and its value in the store
     * 
     * @param   string  $key    the key name
     * @param   mixed   $value  the value of the key
     * @return  void
     */
    public function __set($key, $value)
    {
        if ( empty($key) ) {
            $key = $this->count();
        }

        $this[$key] = is_array($value) ? new Prototype($value) : $value;
    }

    /**
     * Called when you try to access an object as a function
     * 
     * @param   string  $key    the key that will be called as a function
     * @return  mixed
     */
    public function __invoke($key)
    {
        return $this->__get($key);
    }

    /**
     * Whether the requested key exists or not
     * 
     * @param   string  $key    the key to check
     * @return  bool
     */
    public function __isset($key)
    {
        return isset($this[$key]);
    }

    /**
     * Remove a key from the store
     * 
     * @param   string   $key   the key to be removed
     * @return  void
     */
    public function __unset($key)
    {
        unset($this[$key]);
    }

    /**
     * Return a serialized string of this object
     * 
     * @return  string
     */
    public function __toString()
    {
        return serialize($this);
    }

    /**
     * @see \ArrayObject::offsetSet()
     */
    public function offsetSet($key, $value)
    {
        $key    =   $this->__key($key);
        parent::offsetSet($key, $value);
    }

    /**
     * @see \ArrayObject::offsetGet()
     */
    public function offsetGet($key)
    {
        $key    =   $this->__key($key);
        return parent::offsetGet($key);
    }

    /**
     * @see \ArrayObject::offsetUnset()
     */
    public function offsetUnset($key)
    {
        $key    =   $this->__key($key);
        parent::offsetUnset($key);
    }

    /**
     * @see \ArrayObject::offsetExists()
     */
    public function offsetExists($key)
    {
        $key    =   $this->__key($key);
        return parent::offsetExists($key);
    }

    /**
     * Call a key as a method from the store
     * 
     * @param   string  $key    the key to call
     * @param   array   $args   the arguments to pass to the function
     * @return  mixed
     */
    public function __call($key, $args)
    {
        return is_callable($c = $this->__get($key)) ? call_user_func_array($c, $args) : null;
    }

    /**
     * Call a key as a function statically
     * 
     * @param   string  $key    the key to call
     * @param   array   $args   the arguments to pass to the function
     * @return  mixed
     */
    public static function __callStatic($key, $args)
    {
        return self::instance()->__call($key, $args);
    }

    /**
     * Return the instance of the called class
     * 
     * @return  this
     */
    public static function instance()
    {
        $class = get_called_class();

        if ( ! isset(self::$instances[$class]) ) {
            self::$instances[$class] = new $class;
        }

        return self::$instances[$class];
    }

    /**
     * @see \ArrayObject::exchangeArray()
     */
    public function import($input)
    {
        $this->exchangeArray($input);
        return $this;
    }

    /**
     * Return the current object as an array
     * 
     * @return  array
     */
    public function toArray()
    {
        return (array) $this;
    }
}

// ------------------------------

/**
 * EventEmitter
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class EventEmitter extends Prototype
{
    /**
     * The maximum listeners per event
     * @var integer
     */
    private $maxListeners =   0;

    /**
     * The listeners array
     * @var array
     */
    private $listeners    =   null;

    /**
     * Adds a listener to the listeners array for the specified event
     * 
     * @param   string      $event      the event name
     * @param   callable    $listener   the listener callback
     * @param   bool        $once       whether to register this listener as a one-time listener or not
     * @return  this
     */
    public function addListener($event, $listener, $once = false)
    {
        $event = strtolower($event);

        if ( ($this->maxListeners > 0) && ! ($this->listenerCount($event) <= $this->maxListeners) ) {
            return $this;
        }

        $this->listeners[$event][] = array($listener, $once);

        return $this;
    }

    /** @see EventEmitter::addListener() */
    public function on($event, $listener, $once = false)
    {
        return call_user_func_array(array($this, 'addListener'), func_get_args());
    }

    /** Alias of Emitter::addListener() but registers a one-time event */
    public function once($event, $listener)
    {
        return $this->addListener($event, $listener, true);
    }

    /**
     * Remove a registered listener from the specified event
     * 
     * @param   string      $event
     * @param   callable    $listener
     * @return  this
     */
    public function removeListener($event, $listener)
    {
        $event = strtolower($event);
        unset($this->listeners[$event][array_search($listener, $this->listeners[$event])]);
        return $this;
    }

    /**
     * Removes all listeners, or those of the specified event
     * 
     * @param   string      $event
     * @return  this
     */
    public function removeAllListeners($event = null)
    {
        if ( !empty($event) )
            unset($this->listeners[$event]);
        else
            $this->listeners = array();
        return $this;
    }

    /**
     * Sets the maximum listeners per-event
     * 
     * @param   integer     $n
     * @return  this
     */
    public function setMaxListeners($n)
    {
        $this->maxListeners =   abs((int) $n);
        return $this;
    }

    /**
     * Returns an array of listeners for the specified event
     * 
     * @param   string      $event
     * @return  array
     */
    public function &listeners($event)
    {
        $array = array();

        if ( ! isset($this->listeners[$event = strtolower($event)]) ) {
            return $array;
        } else {
            return $this->listeners[$event];
        }
    }

    /**
     * Return the number of listeners for a given event
     * 
     * @param   string      $event
     * @return  integer
     */
    public function listenerCount($event)
    {
        return sizeof($this->listeners($event));
    }

    /**
     * Execute each of the listeners in order with the supplied arguments
     * 
     * @param   string      $event      the event to emit its listeners
     * @param   mixed       $args       the argument(s) passed to the listeners
     * @param   mixed       $return     the default value to return
     * @return  mixed
     */
    public function emit($event, $args = null, $return = null)
    {
        $event  =   strtolower($event);

        foreach ( $this->listeners($event) as $id => $listener )
        {
            $return = call_user_func_array($listener[0], array_merge((array) $args, array($return)));

            if ( $listener[1] ) {
                unset($this->listeners[$event][$id]);
            }

            if ( $return === false ) {
                return $return;
            }
        }

        return $return;
    }
}

// ------------------------------

/**
 * Console
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class Console
{
    /**
     * Printing to the stdout
     * 
     * @param   mixed   $data
     * @param   mixed   $args
     * @return  void
     */
    public static function log($data, $args = null)
    {
        $data = (is_array($data) || is_object($data)) ? print_r($data, 1) : $data;
        echo (!is_null($args) ? vsprintf($data, $args) : $data) . "\r\n";
    }

    /** Alias of Console::log() */
    public static function error($data, $args = null)
    {
        static::log($data, $args);
    }

    /**
     * Read/Write from/to the console window
     * 
     * @return string
     */
    public static function input($data = null, $args = null)
    {
        if ( $data ) {
            static::log($data, $args);
        }

        return fgets(STDIN);
    }
}

// ------------------------------

/**
 * Timers
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class Timers extends Prototype
{
    /**
     * Holds array of registered timeouts timers
     * @var array
     */
    protected   $timeouts   =   array();

    /**
     * Holds array of registered intervals timers
     * @var array
     */
    protected   $intervals  =   array();

    /**
     * To schedule execution of a one-time callback after delay milliseconds
     * 
     * @param   callable    $callback
     * @param   integer     $delay
     * @return  object
     */
    public function setTimeout($callback, $delay)
    {
        $info           =   new \stdClass;
        $info->id       =   is_object($callback) ? md5(spl_object_hash($callback)) : md5(serialize($callback));
        $info->cb       =   $callback;
        $info->time     =   microtime(true);
        $info->delay    =   (float) $delay;

        $this->timeouts[$info->id] = $info;

        return $info;
    }

    /**
     * Stops a timeout from triggering
     * 
     * @param   object  $timeoutObject
     * @return  this
     */
    public function clearTimeouts(\stdClass $timeoutObject)
    {
        if ( ! empty($timeoutObject->id) ) {
            unset($this->timeouts[$info->id]);
        }

        return $this;
    }

    /**
     * To schedule execution of a callback after delay milliseconds
     * 
     * @param   callable    $callback
     * @param   integer     $delay
     * @return  object
     */
    public function setInterval($callback, $delay)
    {
        $info           =   new \stdClass;
        $info->id       =   is_object($callback) ? md5(spl_object_hash($callback)) : md5(serialize($callback));
        $info->cb       =   $callback;
        $info->time     =   microtime(true);
        $info->delay    =   (float) $delay;

        $this->intervals[$info->id] = $info;

        return $info;
    }

    /**
     * Stops an interval from triggering
     * 
     * @param   object  $intervalObject
     * @return  this
     */
    public function clearIntervals(\stdClass $intervalObject)
    {
        if ( ! empty($intervalObject->id) ) {
            unset($this->timeouts[$info->id]);
        }

        return $this;
    }

    /**
     * Call and check timers
     * 
     * @return  this
     */
    public function tick()
    {
        $now        =   microtime(true);

        foreach ( $this->timeouts as $id => $timer ) {
             if ( ($now - $timer->time) >= $timer->delay ) {
                is_callable($timer->cb) &&
                    call_user_func($timer->cb, $timer);
                unset($this->timeouts[$id]);
             }
        }

        foreach ( $this->intervals as $id => $timer ) {
             if ( ($now - $timer->time) >= $timer->delay ) {
                is_callable($timer->cb) &&
                    call_user_func($timer->cb, $timer);
                $this->intervals[$id]->time = microtime(true);
             }
        }

        return $this;
    }
}

// ------------------------------

/**
 * IOLoop
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class IOLoop extends Timers
{
    /**
     * A stream type that we can read from
     * @var integer
     */
    const READ  = 0;

    /**
     * A stream type that we can write to
     * @var integer
     */
    const WRITE = 1;

    /**
     * The streams container
     * @var array
     */
    protected $streams;

    /**
     * The handlers container
     * @var array
     */
    protected $handlers;

    /**
     * Whether the loop is running or not
     * @var bool
     */
    protected $running;

    /**
     * An event-base resource
     * @var resource 
     */
    protected $evbase;

    /**  Constructor */
    public function __construct()
    {
        parent::__construct();

        $this->streams = $this->handlers = array(
            self::READ  =>  array(),
            self::WRITE =>  array(),
        );

        $this->running = true;

        if ( function_exists('event_base_new') ) {
            $this->evbase = event_base_new();
        }
    }

    /**
     * Register a new stream
     * 
     * @param   resource    $stream
     * @param   integer     $type
     * @param   callable    $handler
     * @return  this
     */
    public function add($stream, $type, $handler)
    {

        if ( ! is_resource($stream) ) {
            return $this;
        }

        $i = (int) $stream;

        // using libevent
        if ( $this->evbase )
        {
            $event  =   event_new();
            $flags  =   ($type == self::READ) ? EV_READ : EV_WRITE;
            $loop   =   $this;

            event_set($event, $stream, $flags | EV_PERSIST, array($this, '__handler'), $handler);
            event_base_set($event, $this->evbase);
            event_add($event);

            $this->streams[$type][$i] = $event;
        }

        // using stream_select
        else
        {
            $this->streams[$type][$i]   =   $stream;
            $this->handlers[$type][$i]  =   $handler;
        }

        return $this;
    }

    /**
     * Unregister a stream
     * 
     * @param   resource    $stream
     * @param   integer     $type
     * @return  this
     */
    public function remove($stream, $type = null)
    {
        $i      =   (int) $stream;
        $type   =   is_null($type) ? array(self::READ, self::WRITE) : array($type);

        // using libevent
        if ( $this->evbase )
        {
            foreach ( $type as $t ) {
                if ( isset($this->streams[$t]) ) {
                    event_del($this->streams[$t][$i]);
                    event_free($this->streams[$t][$i]);
                }
            }
        }

        // using stream_select
        else
        {
            foreach ( $type as $t ) {
                unset($this->streams[$t][$i], $this->handlers[$t][$i]);
            }
        }

        return $this;
    }

    /**
     * Watch the stream for changes
     * 
     * @param   integer     $timeout
     * @return  this
     */
    public function watch($timeout)
    {
        // using libevent
        if ( $this->evbase )
        {
            event_base_loop($this->evbase, EVLOOP_ONCE | EVLOOP_NONBLOCK);
        }

        // using stream_select
        else
        {
            $read   =   $this->streams[self::READ];
            $write  =   $this->streams[self::WRITE];
            $error  =   array();
    
            if ( ($read || $write) && stream_select($read, $write, $error, $timeout, $timeout * 1000000) )
            {
                foreach ( $read as $stream ) {
                    $i = (int) $stream;
                    call_user_func($this->handlers[self::READ][$i], $stream, $this);
                }
    
                foreach ( $write as $stream ) {
                    $i = (int) $stream;
                    call_user_func($this->handlers[self::WRITE][$i], $stream, $this);
                }
            }
        }

        return $this;
    }

    /**
     * Start the inifity loop
     * 
     * @param   integer     $timeout
     * @param   integer     $delay
     * @return  this
     */
    public function run($timeout = 5, $delay = 5000)
    {
        while ( $this->running ) {
            $this->watch($timeout);
            $this->tick();
            usleep($delay);
        }

        return $this;
    }

    /**
     * Stop the loop
     * 
     * @return this
     */
    public function stop()
    {
        $this->running = false;
        return $this;
    }

    /**
     * A proxy the libevent handler instead of direct closure to avoid "cannot remove active lambada"
     * 
     * @return this
     */
    public function __handler()
    {
    	$argv	=	func_get_args();
    	return $argv[2]($argv[0], $this);
    }
}

// ------------------------------

/**
 * IOStream
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 * 
 * @event       "error"       when there is aan error
 * @event       "data"        when there is any data available
 * @event       "end"         when the stream reaches end of data being read from readable stream
 * @event       "flush"       when the data is being flushed/drained
 * @event       "drain"       when the stream data is drained
 * @event       "close"       when the stream is being closed
 */
Class IOStream extends EventEmitter
{
    /**
     * A type that tells Horus that the specified stream is readable
     * @var integer
     */
    const READABLE  =   0;

    /**
     * A type that tells Horus that the specified stream is writable
     * @var integer
     */
    const WRITABLE  =   1;

    /**
     * A type that tells Horus that the specified stream is readable & writable
     * @var integer
     */
    const DUPLEX    =   2;

    /**
     * An instance of the IOLoop
     * @var IOLoop
     */
    protected   $ioloop;

    /**
     * The data to be written
     * @var string
     */
    protected   $data;

    /**
     * The stream type
     * @var integer
     */
    protected   $type;

    /**
     * Whether the stream is closed or not
     * @var bool
     */
    protected   $closed;

    /**
     * The stream resource
     * @var resource
     */
    public      $stream;

    /**
     * The length of data used while reading
     * @var integer
     */
    public      $bufferSize;

    /**
     * The function used to reead from the stream
     * @var callable
     */
    public      $reader;

    /**
     * The function used to write to the stream
     * @var callable
     */
    public      $writer;

    /**
     * Constructor
     * 
     * @param   IOLoop      $ioloop
     * @param   resource    $stream
     * @param   integer     $type
     */
    public function __construct(IOLoop $ioloop, $stream, $type = IOStream::DUPLEX)
    {
        parent::__construct();

        $this->stream       =   $stream;
        $this->ioloop       =   $ioloop;
        $this->data         =   "";
        $this->bufferSize   =   4096;
        $this->type         =   $type;
        $this->reader       =   'fread';
        $this->writer       =   'fwrite';

        stream_set_blocking($stream, 0);

        if ( ($type == self::READABLE) || ($type == self::DUPLEX) ) {
            $ioloop->add($stream, IOLoop::READ, array($this, '__read'));
            stream_set_read_buffer($stream, 0);
        }

        if ( ($type == self::WRITABLE) || ($type == self::DUPLEX) ) {
            $ioloop->add($stream, IOLoop::WRITE, array($this, '__write'));
            stream_set_write_buffer($stream, 0);
        }
    }

    /**
     * Whether the current stream is readable
     * 
     * @return  bool
     */
    public function isReadable()
    {
        return ($this->type == self::READABLE) || $this->isDuplex();
    }

    /**
     * Whether the current stream is writable
     * 
     * @return  bool
     */
    public function isWritable()
    {
        return ($this->type == self::WRITABLE) || $this->isDuplex();
    }

    /**
     * Whether the current stream is duplex (readable & writable)
     * 
     * @return  bool
     */
    public function isDuplex()
    {
        return $this->type == self::DUPLEX;
    }

    /**
     * Remove the stream from the ioloop read-set
     * 
     * @return  this
     */
    public function pause()
    {
        if ( $this->type == self::DUPLEX ) {
            $this->type = self::WRITABLE;
        } else {
            $this->type = -1;
        }

        $this->ioloop->remove($this->stream, IOLoop::READ);
        return $this;
    }

    /**
     * Add the stream to the ioloop write-set
     * 
     * @return  this
     */
    public function resume()
    {
        if ( $this->type == self::WRITABLE ) {
            $this->type = self::DUPLEX;
        }

        $this->ioloop->add($this->stream, IOLoop::READ);
        return $this;
    }

    /**
     * Asynchronous stream writer 
     * 
     * @param   string  $data
     * @return  this
     */
    public function write($data)
    {
        $this->data .= $data;
        return $this;
    }

    /**
     * Copy all data from the current stream to the specified one
     * 
     * @param   IOStream    $dest
     * @return  this
     */
    public function pipe(IOStream $dest)
    {
        $this->on('data', function($data, $src) use($dest) {
            $dest->write($data);
        });

        return $this;
    }

    /**
     * Copy all data from the specified stream to the current one
     * 
     * @param   IOStream    $src
     * @return  this
     */
    public function unpipe(IOStream $src)
    {
        return $src->pipe($this);
    }

    /**
     * Close the stream and remove its all listeners & handlers
     * 
     * @return  this
     */
    public function close()
    {
        $this->emit('close', array($this));

        $this->data     =   "";
        $this->type     =   -1;
        $this->closed   =   true;

        $this->ioloop->remove($this->stream);
        $this->removeAllListeners();

        is_resource($this->stream) && fclose($this->stream);

        return $this;
    }

    /**
     * Whether this stream is closed or not
     * 
     * @return  bool
     */
    public function closed()
    {
        return $this->closed;
    }

    /** @ignore */
    public function __read()
    {
        if ( ! $this->isReadable() ) {
            return $this;
        }

        if ( ! is_resource($this->stream) ) {
            $this->emit("error", array('cannot read from un-readable stream', $this));
            return $this;
        }

        $fread  =   $this->reader;
        $data   =   $fread($this->stream, $this->bufferSize);
        $feof   =   ($data === false) || ($data === "") || feof($this->stream);

        if ( $feof ) {
            $this->emit('end', array($this));
            $this->pause();
            return $this;
        }

        $this->emit('data', array($data, $this));

        return $this;
    }

    /** @ignore */
    public function __write()
    {
        if ( ! $this->isWritable() || ($this->data == "") ) {
            return $this;
        }

        if ( ! is_resource($this->stream) ) {
            $this->emit("error", array('cannot write to un-writable stream', $this));
            return $this;
        }

        $this->emit('flush', array($this->data, $this));

        $fwrite         =   $this->writer;
        $sent           =   (int) @$fwrite($this->stream, $this->data, $len = strlen($this->data));
        $this->data     =   (string) substr($this->data, $sent);

        if ( ($sent === 0) && $len ) {
            $this->emit('error', array('cannot write the stream', $this));
            return $this;
        }

        if ( strlen($this->data) ) {
            return $this;
        }

        $this->emit('drain', array($this));

        return $this;
    }
}

// ------------------------------

/**
 * IOClient
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 * 
 * @event       "connection"  when the connection is created
 */
Class IOClient extends IOStream
{
    /**
     * Constructor
     * 
     * @param   IOLoop  $ioloop
     * @param   integer $type
     */
    public function __construct(IOLoop $ioloop, $type = IOClient::DUPLEX)
    {
        $this->ioloop   =   $ioloop;
        $this->type     =   $type;
    }

    /**
     * Create a connection to the specified address
     * 
     * @param   string  $address
     * @param   array   $context
     * @return  this
     */
    public function connect($address, array $context = array())
    {
        $context    =   stream_context_create($context);
        $stream     =   stream_socket_client($address, $errno, $errrstr, 10, STREAM_CLIENT_CONNECT|STREAM_CLIENT_ASYNC_CONNECT, $context);

        if ( ! is_resource($stream) || $errrstr ) {
            $this->emit('error', array($errrstr, $this));
            return $this;
        }

        parent::__construct($this->ioloop, $stream, $this->type);

        $this->reader    =   'fgets';
        $this->writer    =   'stream_socket_sendto';

        $this->on('close', function($client) {
            stream_socket_shutdown($client->stream, STREAM_SHUT_RDWR);
        });

        $this->emit('connection', array($this));

        return $this;
    }
}

// ------------------------------

/**
 * IOServer
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 * 
 * @event       "error"       when there is any error
 * @event       "listening"   when the server starts listening
 * @event       "connection"  when there is a new connection
 */
Class IOServer extends EventEmitter
{
    /**
     * An instance of the IOLoop
     * @var IOLoop
     */
    protected $iloop;

    /**
     * The master socket (server) resource
     * @var resource
     */
    public $stream;

    /**
     * Constructor
     * 
     * @param   IOLoop  $ioloop
     */
    public function __construct(IOLoop $ioloop)
    {
        parent::__construct();

        $this->iloop = $ioloop;
    }

    /**
     * Start listening for new connections
     * 
     * @param   string  $address
     * @param   array   $context
     * @return  this
     */
    public function listen($address, array $context = array())
    {
        $address        =   is_numeric($address) ? "tcp://0.0.0.0:{$address}" : $address;
        $context        =   stream_context_create($context);
        $this->stream   =   @ stream_socket_server($address, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);
        $server         =   $this;

        if ( ! is_resource($this->stream) || $errstr ) {
            $this->emit('error', array($errstr, $this));
            return $this;
        }

        $this->emit('listening', array($this));

        $this->iloop->add($this->stream, IOLoop::READ, function($master, $ioloop) use($server) {
            $client            = 	stream_socket_accept($master, 0);
            $client            =   	new IOStream($ioloop, $client, IOStream::DUPLEX);
            $client->reader    =   'fgets';
            $client->writer    =   'stream_socket_sendto';
            $client->on('close', function($client){
                stream_socket_shutdown($client->stream, STREAM_SHUT_RDWR);
            });
            $server->emit('connection', array($client));
        });

        return $this;
    }

    /**
     * Close the server
     * 
     * @return this
     */
    public function close()
    {
        fclose($this->stream);
        $this->removeAllListeners();
        return $this;
    }
}

// ------------------------------

/**
 * Frame
 *
 * @package     Plus
 * @author      Mohammed Al Ashaal
 * @since       1.0.0
 */
Class Frame extends IOLoop
{
    /**
     * Create a new instance of Prototype
     * 
     * @return  Prototype
     */
    public function prototype()
    {
        return new Prototype;
    }

    /**
     * Create a new instance of IOStream
     * 
     * @param   resource    $stream
     * @param   integer     $type
     * @return  IOStream
     */
    public function iostream($stream, $type = IOStream::DUPLEX)
    {
        return new IOStream($this, $stream, $type);
    }

    /**
     * Create a new instance of IOClient
     * 
     * @param   integer     $type
     * @return  IOClient
     */
    public function ioclient($type = IOClient::DUPLEX)
    {
        return new IOClient($this, $type);
    }

    /**
     * Create a new instance of IOServer
     * 
     * @return  IOServer
     */
    public function ioserver()
    {
        return new IOServer($this);
    }
}

// ------------------------------
