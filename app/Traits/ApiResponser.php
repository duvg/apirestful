<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;


trait ApiResponser
{
	// Respose for success operation
	private function successResponse($data, $code)
	{
		return response()->json($data, $code);
	}

	// Response for error in proccess
	protected function errorResponse($message, $code)
	{
		return response()->json(['error' => $message, 'code' => $code], $code);
	}

	// Return a collection of results 
	protected function showAll(Collection $collection, $code = 200)
	{
		if ($collection->isEmpty()) 
		{
			return $this->successResponse(['data' => $collection], $code);
		}
		$transformer = $collection->first()->transformer;
		$collection = $this->transformData($collection, $transformer);


		return $this->successResponse($collection, $code);
	}

	// Return a record
	protected function showOne(Model $instance, $code = 200)
	{
		$transformer = $instance->transformer;
		$data = $this->transformData($instance, $transformer);
		return $this->successResponse($data, $code);
	}

	// Return a string
	protected function showMessage($message, $code)
	{
		return $this->successResponse(['data' => $message], $code);
	}

	// Transform data
	protected function transformData($data, $transformer)
	{
		$transformation = fractal($data, new $transformer);

		return $transformation->toArray();

	}


}