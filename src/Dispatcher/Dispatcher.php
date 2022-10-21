<?php

namespace App\Dispatcher;

use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Annotation\PathVariable;
use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionMethod;

class Dispatcher
{
    private array $controllers = [];

    public function __construct()
    {
        $filesInFolder = scandir(ROOT_DIR . 'Controller');
        foreach ($filesInFolder as $fileName) {
            if (preg_match("/^[A-Z][a-z]+Controller\.php$/", $fileName)) {
                include ROOT_DIR . 'Controller\\' . $fileName;
                $fileName = preg_replace("/\.php/", '', $fileName);
                $fileName = "App\\Controller\\$fileName";
                $this->controllers[] = new $fileName();
            }
        }
    }

    public function dispatch(string $request): mixed
    {
        $requestData = $this->parseRequest($request);

        try {
            $reader = new AnnotationReader();

            foreach ($this->controllers as $controller) {
                $class = $controller::class;
                $methods = get_class_methods($controller);
                foreach ($methods as $method) {
                    $refMethod = new ReflectionMethod("$class::$method");
                    $mapping = $reader->getMethodAnnotation($refMethod, RequestMapping::class);
                    if ($mapping) {
                        $methodsMethod = $mapping->method;
                        $methodsPath = $mapping->path;
                        if ($methodsMethod == $requestData['method']) {
                            $pathVariable = $reader->getMethodAnnotation($refMethod, PathVariable::class);
                            if ($pathVariable) {
                                $variableName = $pathVariable->variableName;
                                $requestPathChunked = preg_split("/\//", $requestData['path']);
                                $methodsPathChunked = preg_split("/\//", $methodsPath);
                                $variableIndex = array_search('{' . $variableName . '}', $methodsPathChunked);
                                $variableValue = $requestPathChunked[$variableIndex];
                                $pathReplaced = preg_replace('/\{' . $variableName . '\}/', $variableValue, $methodsPath);
                                if ($requestData['path'] == $pathReplaced) {
                                    if ($refMethod->getReturnType() != null && $refMethod->getReturnType() != 'void') {
                                        return $this->buildHttpResponse($controller->$method($variableValue));
                                    } else {
                                        $controller->$method($variableValue);

                                        return $this->buildHttpResponse();
                                    }
                                }
                            }
                            $requestBodyVariable = $reader->getMethodAnnotation($refMethod, RequestBodyVariable::class);
                            if ($requestBodyVariable && $methodsPath == $requestData['path']) {
                                if ($refMethod->getReturnType() != null && $refMethod->getReturnType() != 'void') {
                                    return $this->buildHttpResponse($controller->$method($requestData['body']));
                                } else {
                                    $controller->$method($requestData['body']);

                                    return $this->buildHttpResponse();
                                }
                            }
                            if ($methodsPath == $requestData['path']) {
                                if ($refMethod->getReturnType() != null && $refMethod->getReturnType() != 'void') {
                                    return $this->buildHttpResponse($controller->$method());
                                } else {
                                    $controller->$method();

                                    return $this->buildHttpResponse();
                                }
                            }
                        }
                    }
                }
            }

            return $this->buildErrorResponse(new Exception('Request routing appears to be invalid', 400));
        } catch (Exception $e) {
            return $this->buildErrorResponse($e);
        }
    }

    private function parseRequest(string $request): array
    {
        $result = [];

        $splitRequest = preg_split('/\r\n/', $request);


        $head = $splitRequest[0];
        $chunks = preg_split('/\s/', $head);
        $result['method'] = $chunks[0];
        $result['path'] = $chunks[1];

        $result['body'] = json_encode(array_pop($splitRequest));

        if (json_decode($result['body'])) {
            $result['body'] = json_decode($result['body'], true);
        } else {
            $result['body'] = null;
        }

        if ($result['method'] == 'OPTIONS') {
            foreach ($splitRequest as $line) {
                if (preg_split('/\:\s/', $line)[0] == 'Access-Control-Request-Method') {
                    $result['method'] = preg_split('/\:\s/', $line)[1];
                }
            }
        }

        return $result;
    }

    private function buildHttpResponse(string $responseBody = ''): string
    {
        return 'HTTP/1.1 ' . ($responseBody !== '' ? 200 : 204) . "\r\n" .
            'Date: ' . date('D, d M Y H:i:s e') . "\r\n" .
            ($responseBody === '' ? '' : ('Content-Length: ' . strlen($responseBody) . "\r\n")) .
            ($responseBody === '' ? '' : ("Content-Type: application/json\r\n")) .
            "Connection: Closed\r\n" .
            ($responseBody === '' ? '' : ("\r\n$responseBody"));
    }

    private function buildErrorResponse(Exception $e): string
    {
        $jsonException = json_encode($e);

        return 'HTTP/1.1 ' . $e->getCode() . "\r\n" .
            'Date: ' . date('D, d M Y H:i:s e') . "\r\n" .
            "Connection: Closed\r\n";
    }
}
