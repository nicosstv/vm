<?php
namespace Vtex\VtexMagento\Model;

use \Exception;
use \Psr\Log\LoggerInterface;
use \Magento\SalesRule\Api\Data\RuleInterface;
use \Magento\SalesRule\Api\Data\CouponInterface;
use \Magento\Framework\Exception\InputException;
use \Magento\SalesRule\Api\RuleRepositoryInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\SalesRule\Api\CouponRepositoryInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\SalesRule\Api\Data\RuleInterfaceFactory;

class Coupon
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var CouponInterface
     */
    protected $coupon;

    public function __construct(
        CouponRepositoryInterface $couponRepository,
        RuleRepositoryInterface $ruleRepository,
        RuleInterfaceFactory $rule,
        CouponInterface $coupon,
        LoggerInterface $logger
    ) {
        $this->couponRepository = $couponRepository;
        $this->ruleRepository = $ruleRepository;
        $this->rule = $rule;
        $this->coupon = $coupon;
        $this->logger = $logger;
    }

    /**
     * Create Rule
     *
     * @return void
     */
    public function createRule($discount)
    {
        $newRule = $this->rule->create();
        $newRule->setName($discount['name'])
            ->setDescription($discount['name'])
            ->setIsAdvanced(true)
            ->setStopRulesProcessing(true)
            ->setDiscountQty(20)
            ->setCustomerGroupIds([0, 1, 2])
            ->setWebsiteIds([1])
            ->setIsRss(1)
            ->setUsesPerCoupon(0)
            ->setDiscountStep(0)
            ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
            ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART)
            ->setDiscountAmount($discount['value'])
            ->setIsActive(true);

        try {
            $ruleCreate = $this->ruleRepository->save($newRule);
            //If rule generated, Create new Coupon by rule id
            if ($ruleCreate->getRuleId()) {
               return $this->createCoupon($ruleCreate->getRuleId(), $discount);
            }
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }

        return null;
    }

    /**
     * Create Coupon by Rule id.
     *
     * @param int $ruleId
     *
     * @return int|null
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createCoupon(int $ruleId, $discount) {
        /** @var CouponInterface $coupon */
        $coupon = $this->coupon;
        $coupon->setCode($discount['name'])
            ->setIsPrimary(1)
            ->setRuleId($ruleId);

        /** @var CouponRepositoryInterface $couponRepository */
        $coupon = $this->couponRepository->save($coupon);
        return $coupon;
    }
}
