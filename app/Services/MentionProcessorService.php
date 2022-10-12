<?php

namespace App\Services;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MentionProcessorService
{
	private string $content;
	private string $mentionSymbol;
	private Model $mentionableKlass;
	private array $mentionableColumns;
	private bool $withTrashedResource;
	private string $unreplaceableMentionReplacement;

	private string $requiredContract = 'App\Contracts\MentionableContract';

	/**
	 * @param Model $mentionableKlass
	 * @param string $mentionSymbol
	 * @param bool $withTrashedResource
	 */
	public function __construct(Model $mentionableKlass, string $mentionSymbol = '@', string $unreplaceableMentionReplacement = '', bool $withTrashedResource = true)
	{
		$this->mentionableKlass = $mentionableKlass;
		$this->mentionSymbol = $mentionSymbol;
		$this->withTrashedResource = $withTrashedResource;
		$this->unreplaceableMentionReplacement = $unreplaceableMentionReplacement;

		$this->checkIfMentionableContractImplemented();

		$this->mentionableColumns = $this->mentionableKlass::mentionableColumns();
	}

	/**
	 * Set the content message which may contain mentions
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function setContent(string|null $content): void
	{
		if (empty($content)) $content = '';

		$this->content = $content;
	}

	/**
	 * @param array $replaceIdWithAttributes
	 * @param bool $replaceInvalidMention
	 *
	 * @return string
	 */
	public function getProcessedContent(array $replaceIdWithAttributes, bool $replaceInvalidMention = true): string
	{
		$content = $this->content;
		$content = $this->replaceAllMentionsWithModelAttribute($content, $replaceIdWithAttributes);
		if ($replaceInvalidMention) $content = $this->hideUnreplaceableMentions($content);

		return preg_replace('/\s+/', ' ', $content);
	}

	/**
	 * Get all mentioned resources while append the trashed status
	 *
	 * @return Illuminate\Support\Collection
	 */
	public function getMentionables(): Collection
	{
		return $this->loadMentionables()->map(function ($resource) {
			$data = $resource->only($this->mentionableColumns);
			$data['trashed'] = $resource->trashed();

			return $data;
		});
	}

	/**
	 * Load all mentioned resources from database
	 *
	 * @return Illuminate\Support\Collection
	 */
	public function loadMentionables(): Collection
	{
		$model = $this->mentionableKlass;

		if ($this->withTrashedResource) $model = $model->withTrashed();

		return $model->whereIn('id', $this->mentionData('id'))->get();
	}

	/**
	 * Get all unique mentions with the matching patterns
	 *
	 * @return Illuminate\Support\Collection
	 */
	public function getMentions(): Collection
	{
		return $this->mentionData('original');
	}

	/**
	 * Get all the mentions based on pattern(), you may get the extracted data using the group name as well.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	private function mentionData(string $key): Collection
	{
		preg_match_all($this->pattern('\d+'), $this->content, $mentioned);

		return collect($mentioned[$key])->unique();
	}

	private function symbolTag(): string
	{
		return $this->mentionSymbol;
	}

	private function pattern(string $identifierPattern): string
	{
		return "/(?<original>(?<trigger>{$this->symbolTag()})\[(?<name>([^[]*))\]\((?<id>({$identifierPattern}))\))/";
	}

	/**
	 * Make sure models with implements of mentionable contract are the only one allowed to extract mentions
	 * if the model doesn't implement mentionable contract then throw an error
	 *
	 * @return void
	 */
	private function checkIfMentionableContractImplemented(): void
	{
		$klassImplements = class_implements($this->mentionableKlass);

		if (in_array($this->requiredContract, $klassImplements)) return;

		$klassName = get_class($this->mentionableKlass);
		throw new ErrorException("{$klassName} doesn't implement {$this->requiredContract}");
	}

	/**
	 * Replace all mention with desirred attributes, it's accept array so you can mixmatch it
	 * ex: 	['firstname', 'lastname'] will resulting with @this is firstname this is lastname
	 * 		['firstname', 'username'] will resulting with @this is firstname this_is_username
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	private function replaceAllMentionsWithModelAttribute(string $content, array $attributes): string
	{
		$replacedContent = $content;

		foreach ($this->getMentionables() as $mentionable) {
			$replaceMentions = implode(' ', array_map(function ($attr) use ($mentionable) {
				return $mentionable[$attr];
			}, $attributes));

			$replacedContent = $this->replaceMentions($mentionable['id'], "{$this->mentionSymbol}{$replaceMentions}", $replacedContent);
		}

		return $replacedContent;
	}

	/**
	 * Replace mentions to desired text
	 *
	 * @param string $identifierPattern
	 * @param string $replacement
	 * @param string $subject
	 *
	 * @return string
	 */
	private function replaceMentions(string $identifierPattern, string $replacement, string $subject): string
	{
		return preg_replace($this->pattern($identifierPattern), $replacement, $subject);
	}

	/**
	 * Replace all identifer after the mention symbol to $unreplaceableMentionReplacement
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function hideUnreplaceableMentions(string $content): string
	{
		return $this->replaceMentions('\S*', $this->unreplaceableMentionReplacement, $content);
	}
}
