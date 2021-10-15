<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;

/**
 * Class Templates
 * @package Klaviyo
 */
class Templates extends KlaviyoAPI
{
    /**
     * Templates endpoint constants
     */
    const ENDPOINT_EMAIL_TEMPLATES = 'email-templates';
    const ENDPOINT_EMAIL_TEMPLATE = 'email-template';
    const CLONE_PATH = 'clone';
    const RENDER_PATH = 'render';
    const SEND_PATH = 'send';

    /**
     * Email template creation arguments
     */
    const NAME = 'name';
    const ID = 'template_id';
    const HTML = 'html';
    const CONTEXT = 'context';
    const FROM_EMAIl = 'from_email';
    const FROM_NAME = 'from_name';
    const SUBJECT = 'subject';
    const TO = 'to';

    /**
     * Returns a list of all the email templates you've created.
     * The templates are returned in sorted order by name.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#get-templates
     *
     * @return mixed
     */
    public function getAllTemplates()
    {
        return $this->v1Request(self::ENDPOINT_EMAIL_TEMPLATES, [], self::HTTP_GET);
    }

    /**
     * Creates a new email template.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#create-template
     *
     * @param $name
     * The name of the email template.
     * @param $html
     * HTML body of the email template.
     * @return mixed
     * @throws KlaviyoException
     */
    public function createNewTemplate($name, $html)
    {
        $params = $this->createRequestBody(
            array(
                self::NAME => $name,
                self::HTML => $html
            )
        );

        return $this->v1Request(self::ENDPOINT_EMAIL_TEMPLATES, $params, self::HTTP_POST);
    }

    /**
     * Updates the name and/or HTML content of a template.
     * Only updates imported HTML templates; does not currently update drag & drop templates.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#update-template
     *
     * @param $templateId
     * The id of the email template to update.
     * @param $name
     * The new name of the email template.
     * @param $html
     * The new HTML content for this template.
     * @return mixed
     * @throws KlaviyoException
     */
    public function updateTemplate($templateId, $name, $html)
    {
        $path = sprintf('%s/%s', self::ENDPOINT_EMAIL_TEMPLATE, $templateId);
        $params = $this->createRequestBody(
            array(
                self::NAME => $name,
                self::HTML => $html
            )
        );

        return $this->v1Request($path, $params, self::HTTP_PUT);
    }

    /**
     * Deletes a given template.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#delete-template
     *
     * @param $templateId
     * The id of the email template to delete.
     * @return mixed
     * @throws KlaviyoException
     */
    public function deleteTemplate($templateId)
    {
        $path = sprintf('%s/%s', self::ENDPOINT_EMAIL_TEMPLATE, $templateId);

        return $this->v1Request($path, [], self::HTTP_DELETE);
    }

    /**
     * Creates a copy of a given template with a new name.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#clone-template
     *
     * @param $templateId
     * The id of the email template to clone.
     * @param $name
     * The new name of the email template.
     * @return mixed
     * @throws KlaviyoException
     */
    public function cloneTemplate($templateId, $name)
    {
        $path = sprintf('%s/%s/%s', self::ENDPOINT_EMAIL_TEMPLATE, $templateId, self::CLONE_PATH);
        $params = $this->filterParams( array(
            self::NAME=> $name
        ) );
        $params = $this->createRequestBody($params);

        return $this->v1Request($path, $params, self::HTTP_POST);
    }

    /**
     * Renders the specified template with the provided data and return HTML and text versions of the email.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#render-template
     *
     * @param $templateId
     * The id of the email template to render.
     * @param $context
     * This is the context your email template will be rendered with.
     * Email templates are rendered with contexts in a similar manner to how Django templates are rendered.
     * This means that nested template variables can be referenced via dot notation and template variables without corresponding context values are treated as falsy and output nothing.
     * @return mixed
     * @throws KlaviyoException
     */
    public function renderTemplate($templateId, $context = "")
    {
        $path = sprintf('%s/%s/%s', self::ENDPOINT_EMAIL_TEMPLATE, $templateId, self::RENDER_PATH);
        $params = $this->createRequestBody(
            array(
                self::CONTEXT => $context,
            )
        );
        return $this->v1Request($path, $params, self::HTTP_POST);
    }

    /**
     * Renders the specified template with the provided data and send the contents in an email via the service specified.
     * This API is intended to test templates only, and is rate limited to the following thresholds: 100/hour, 1,000/day.
     *
     * @link https://apidocs.klaviyo.com/reference/templates#send-template
     *
     * @param $templateId
     * The id of the email template to send.
     * @param $fromEmail
     * The email address the template is sent from.
     * @param $fromName
     * The name of the sender
     * @param $subject
     * The subject of the email
     * @param $to
     * Mixed. string, or JSON encoded array of objects with "email" and "name" keys.
     * Ex:
     * abraham.lincoln@klaviyo.com
     * OR
     * [{"name":"Abraham Lincoln","email":"abraham.lincoln@klaviyo.com"}]
     * @param $context
     *  Optional, JSON object.
     * This is the context your email template will be rendered with.
     * Email templates are rendered with contexts in a similar manner to how Django templates are rendered.
     * This means that nested template variables can be referenced via dot notation and template variables without corresponding context values are treated as falsy and output nothing.
     * Ex:
     * { "name" : "George Washington", "state" : "VA" }
     * @return mixed
     */
    public function sendTemplate($templateId, $fromEmail, $fromName, $subject, $to, $context = "")
    {
        $path = sprintf('%s/%s/%s', self::ENDPOINT_EMAIL_TEMPLATE, $templateId, self::SEND_PATH);
        $params = $this->filterParams( array(
            self::FROM_EMAIl => $fromEmail,
            self::FROM_NAME => $fromName,
            self::SUBJECT => $subject,
            self::TO => $to,
            self::CONTEXT=> $context
        ) );
        $params = $this->createRequestBody($params);

        return $this->v1Request($path, $params, self::HTTP_POST);
    }


}
