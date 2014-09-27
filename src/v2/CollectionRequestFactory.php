<?php

/*
 * Copyright (c) 2014, Etienne Lamoureux
 * All rights reserved.
 * Distributed under the BSD 3-Clause license (http://opensource.org/licenses/BSD-3-Clause).
 */
namespace Crystalgorithm\DurmandScriptorium\v2;

use Crystalgorithm\DurmandScriptorium\utils\Constants;

class CollectionRequestFactory extends RequestFactory
{

    public function idRequest($id)
    {
	$request = $this->buildBaseRequest();
	$query = $request->getQuery();
	$query->set('id', $id);

	return $request;
    }

    public function idsRequest(array $ids)
    {
	if (sizeof($ids) <= Constants::MAX_IDS_SINGLE_REQUEST)
	{
	    $request = $this->buildBaseRequest();
	    $query = $request->getQuery();

	    $formattedIds = $this->formatIds($ids);
	    $query->set('ids', $formattedIds);
	}
	else
	{
	    $request = $this->batchRequest($ids);
	}

	return $request;
    }

    public function pageRequest($page, $pageSize = null)
    {
	$request = $this->buildBaseRequest();
	$query = $request->getQuery();
	$query->set('page', $page);

	if (isset($pageSize) && $pageSize > 0)
	{
	    $query->set('page_size', $pageSize);
	}

	return $request;
    }

    protected function batchRequest(array $ids)
    {
	$requests = array();
	$idChunks = array_chunk($ids, Constants::MAX_IDS_SINGLE_REQUEST);

	foreach ($idChunks as $idChunk)
	{
	    $requests[] = $this->idsRequest($idChunk);
	}

	return $requests;
    }

    protected function formatIds(array $ids)
    {
	return implode(',', $ids);
    }

}