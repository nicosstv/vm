<?php
namespace Vtex\VtexMagento\Renderer;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\RendererInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Vtex\VtexMagento\Service\InvoicePdfGeneratorService;

class PdfRenderer implements RendererInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    /**
     * @var InvoicePdfGeneratorService
     */
    private $invoicePdfGeneratorService;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * Pdf constructor.
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param InvoicePdfGeneratorService $invoicePdfGeneratorService
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        Request $request,
        InvoicePdfGeneratorService $invoicePdfGeneratorService,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->request = $request;
        $this->invoicePdfGeneratorService = $invoicePdfGeneratorService;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Render content in a certain format.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function render($data)
    {
        if (!strstr($this->request->getPathInfo(), '/fulfillment/mgt/invoices')) {
            throw new Exception(__('PDF rendering is not supported for this URI'));
        }

        if (isset($data['entity_id'])) {
            $invoice = $this->invoiceRepository->get($data['entity_id']);
            $pdf = $this->invoicePdfGeneratorService->execute($invoice);

            return $pdf->render();
        }

        return null;
    }

    /**
     * Get MIME type generated by renderer.
     *
     * @return string
     */
    public function getMimeType()
    {
        return 'application/pdf';
    }
}
