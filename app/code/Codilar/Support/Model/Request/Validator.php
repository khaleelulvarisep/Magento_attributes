<?php
declare(strict_types=1);

namespace Codilar\Support\Model\Request;

use Codilar\Support\Model\Request;
use Codilar\Support\Model\ResourceModel\Status as StatusResource;
use Codilar\Support\Model\StatusFactory;
use Magento\Framework\Validator\EmailAddress;

/**
 * Server-side validation for support requests (the form also validates client-side)
 */
class Validator
{
    private const MAX_LENGTH = 255;

    private EmailAddress $emailValidator;
    private StatusFactory $statusFactory;
    private StatusResource $statusResource;

    public function __construct(
        EmailAddress $emailValidator,
        StatusFactory $statusFactory,
        StatusResource $statusResource
    ) {
        $this->emailValidator = $emailValidator;
        $this->statusFactory = $statusFactory;
        $this->statusResource = $statusResource;
    }

    /**
     * @param Request $request
     * @param string|null $originalStatus status stored before this edit; an inactive status may be kept
     * @return string[] error messages, empty when valid
     */
    public function validate(Request $request, ?string $originalStatus = null): array
    {
        $errors = [];
        $labels = [
            'customer_name' => __('Customer Name'),
            'email' => __('Email'),
            'subject' => __('Subject'),
            'message' => __('Message'),
            'status' => __('Status'),
        ];

        foreach ($labels as $field => $label) {
            if (trim((string)$request->getData($field)) === '') {
                $errors[] = __('"%1" is a required field.', $label);
            }
        }
        foreach (['customer_name', 'email', 'subject'] as $field) {
            if (mb_strlen((string)$request->getData($field)) > self::MAX_LENGTH) {
                $errors[] = __('"%1" cannot be longer than %2 characters.', $labels[$field], self::MAX_LENGTH);
            }
        }

        $email = (string)$request->getData('email');
        if ($email !== '' && !$this->emailValidator->isValid($email)) {
            $errors[] = __('"%1" is not a valid email address.', $email);
        }

        $statusCode = (string)$request->getData('status');
        if ($statusCode !== '') {
            $status = $this->statusFactory->create();
            $this->statusResource->load($status, $statusCode, 'code');
            if (!$status->getId()) {
                $errors[] = __('The selected status does not exist.');
            } elseif (!$status->getData('is_active') && $statusCode !== $originalStatus) {
                $errors[] = __('The selected status is inactive.');
            }
        }

        return array_map('strval', $errors);
    }
}
