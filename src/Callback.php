<?php

namespace PlanetTheCloud\MofhCallbackClient;

use PlanetTheCloud\MofhCallbackClient\Exception\InvalidCallbackParameters;
use PlanetTheCloud\MofhCallbackClient\Exception\IpAddressMismatched;

class Callback
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var bool
     */
    protected $shouldHandle = true;

    /**
     * @var callable
     */
    protected $activatedCallback;

    /**
     * @var callable
     */
    protected $suspendedCallback;

    /**
     * @var callable
     */
    protected $reactivatedCallback;

    /**
     * @var callable
     */
    protected $sqlServerCallback;

    /**
     * @var callable
     */
    protected $deletedCallback;

    /**
     * @var callable
     */
    protected $beforeCallback;

    /**
     * @var callable
     */
    protected $afterCallback;

    /**
     * Create a new client
     *
     * @param array $parameters
     *
     * @return Client
     */
    public static function create(array $parameters = [])
    {
        $callback = new self();
        $callback->initialize($parameters);
        return $callback;
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'ip' => '0.0.0.0',
        ];
    }

    /**
     * Initialize the client
     *
     * @param array $parameters
     *
     * @return void
     */
    protected function initialize(array $parameters = [])
    {
        $this->parameters = array_merge($this->getDefaultParameters(), $parameters);
    }

    /**
     * Set the activated callback
     *
     * @param function $callback
     *
     * @return void
     */
    public function onAccountActivated(callable $callback)
    {
        $this->activatedCallback = $callback;
    }

    /**
     * Set the suspended callback
     *
     * @param function $callback
     *
     * @return void
     */
    public function onAccountSuspended(callable $callback)
    {
        $this->suspendedCallback = $callback;
    }

    /**
     * Set the reactivated callback
     *
     * @param function $callback
     *
     * @return void
     */
    public function onAccountReactivated(callable $callback)
    {
        $this->reactivatedCallback = $callback;
    }

    /**
     * Set the sql server callback
     *
     * @param function $callback
     *
     * @return void
     */
    public function onSqlServer(callable $callback)
    {
        $this->sqlServerCallback = $callback;
    }

    /**
     * Set the deleted callback
     *
     * @param function $callback
     *
     * @return void
     */
    public function onAccountDeleted(callable $callback)
    {
        $this->deletedCallback = $callback;
    }

    /**
     * Middleware before callback is handled
     *
     * @param callable $callback
     *
     * @return void
     */
    public function beforeCallback(callable $callback)
    {
        $this->beforeCallback = $callback;
    }

    /**
     * Handle the callback
     *
     * @param array $data
     * @param string $ip Optional IP address, leave blank for auto detect
     *
     * @throws InvalidCallbackParameters
     * @throws IpAddressMismatched
     *
     * @return void
     */
    public function handle(array $data = [], string $ip = null)
    {
        if (isset($this->beforeCallback)) {
            call_user_func($this->beforeCallback, $data, $ip);
        }

        if (!$this->shouldHandle) {
            return;
        }

        $ip = ($ip) ? $ip : $_SERVER['REMOTE_ADDR'];
        if ($ip !== $this->parameters['ip']) {
            throw new IpAddressMismatched("Caller IP address ({$ip}) does not match the allowed IP address.");
        }

        $fields = ['username', 'status', 'comments'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidCallbackParameters("Parameter $field is missing");
            }
            if (!is_string($data[$field])) {
                throw new InvalidCallbackParameters("Parameter $field must be a string");
            }
            if (empty(trim($data[$field]))) {
                throw new InvalidCallbackParameters("Parameter $field must not be empty");
            }
        }

        if ('SQL_SERVER' === $data['comments']) {
            call_user_func($this->sqlServerCallback, $data['username'], $data['status'], $data);
        } else {
            switch ($data['status']) {
                case 'ACTIVATED':
                    call_user_func($this->activatedCallback, $data['username'], $data);
                    break;
                case 'SUSPENDED':
                    call_user_func($this->suspendedCallback, $data['username'], $data['comments'], $data);
                    break;
                case 'REACTIVATE':
                    call_user_func($this->reactivatedCallback, $data['username'], $data);
                    break;
                case 'DELETE':
                    call_user_func($this->deletedCallback, $data['username'], $data);
                    break;
                default:
                    throw new InvalidCallbackParameters("Invalid status: {$data['status']}");
            }
        }

        if (isset($this->afterCallback)) {
            call_user_func($this->afterCallback, $data, $ip);
        }
    }
}
