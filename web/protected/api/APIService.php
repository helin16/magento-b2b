<?php
class APIService extends TService
{
    /**
     * Run
     */
    public function run()
    {
  		$results = $errors = array();
  		try
  		{
            $requestType = strtolower(trim($_SERVER['REQUEST_METHOD']));
            $request = explode("/", substr($_SERVER['PATH_INFO'], 1));
  		    $serivceName = 'API' . ucfirst(trim($this->Request['entityName'])) . 'Service';
  		    $requestedMethod = (isset($this->Request['methodName']) ? trim($this->Request['methodName']) : 'all');
  		    if(preg_match('/^\d+$/', $requestedMethod))
  		    {
  		        $request['entityId'] = $requestedMethod;
  		        $requestedMethod = 'id';
  		    }

  		    $service = new $serivceName();
  		    $method = $requestType . '_' . $requestedMethod;
  		    if(!method_exists($service, $method))
  		        throw new Exception('No such a method: ' . $method . '!');
  		    $results = $service->$method($request);
  		}
  		catch (Exception $ex)
  		{
  		    $errors[] = $ex->getMessage();
  		}
  		$this->getResponse()->flush();
  		$this->getResponse()->appendHeader('Content-Type: application/json');
  		$this->getResponse()->write(StringUtilsAbstract::getJson($results, $errors));
    }
}