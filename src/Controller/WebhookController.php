<?php

namespace CodeCloud\Bundle\ShopifyBundle\Controller;

use CodeCloud\Bundle\ShopifyBundle\Event\WebhookEvent;
use CodeCloud\Bundle\ShopifyBundle\Model\ShopifyStoreManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shopify")
 */
class WebhookController
{
    /**
     * @var ShopifyStoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param ShopifyStoreManagerInterface $storeManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ShopifyStoreManagerInterface $storeManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->storeManager = $storeManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/webhooks", name="codecloud_shopify_webhooks")
     */
    public function handleWebhook(Request $request)
    {
        $topic     = $request->query->get('topic');
        $storeName = $request->query->get('store');

        if (!$topic || !$storeName) {
            throw new NotFoundHttpException();
        }

        if (empty($request->getContent())) {
            throw new BadRequestHttpException('Webhook must have body content');
        }

        $payload = \GuzzleHttp\json_decode($request->getContent(), true);

        $this->eventDispatcher->dispatch(new WebhookEvent(
            $topic,
            $storeName,
            $payload
        ));

        return new Response();
    }
}
