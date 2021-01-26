<?php

class TemplateManager
{
    protected $placeholders = [];

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone $tpl;
        $this->configure($data);
        $replaced->subject = $this->computeText($replaced->subject);
        $replaced->content = $this->computeText($replaced->content);

        return $replaced;
    }

    private function configure(array $data) {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $rawQuote = ($data['quote'] instanceof Quote) ? $data['quote'] : null;
        $user  = ($data['user']  instanceof User) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();

        if ($rawQuote) {
            $quote = QuoteRepository::getInstance()->getById($rawQuote->id);
            $site = SiteRepository::getInstance()->getById($rawQuote->siteId);
            $destination = DestinationRepository::getInstance()->getById($rawQuote->destinationId);

            $this->placeholders['quote:summary_html'] = "<p>$quote->id</p>";
            $this->placeholders['quote:summary'] = $quote->id;
            $this->placeholders['quote:destination_name'] = $destination ? $destination->countryName : '';
            $this->placeholders['quote:destination_link'] = ($site && $destination && $quote) ? "$site->url/$destination->countryName/quote/$quote->id": '';
        }

        if($user) {
            $this->placeholders['user:first_name'] = ucfirst(mb_strtolower($user->firstname));
        }
    }

    private function computeText($text)
    {
        foreach ($this->placeholders as $key => $value) {
            $text = str_replace("[$key]", $value, $text);
        }

        return $text;
    }
}
