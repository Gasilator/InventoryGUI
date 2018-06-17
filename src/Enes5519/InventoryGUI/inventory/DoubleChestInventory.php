<?php

/*
 *      _____                      _                    _____ _    _ _____
 *     |_   _|                    | |                  / ____| |  | |_   _|
 *       | |  _ ____   _____ _ __ | |_ ___  _ __ _   _| |  __| |  | | | |
 *       | | | '_ \ \ / / _ \ '_ \| __/ _ \| '__| | | | | |_ | |  | | | |
 *      _| |_| | | \ V /  __/ | | | || (_) | |  | |_| | |__| | |__| |_| |_
 *     |_____|_| |_|\_/ \___|_| |_|\__\___/|_|   \__, |\_____|\____/|_____|
 *                                                __/ |
 *                                               |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Enes5519
 * @link http://github.com/Enes5519
 */

declare(strict_types=1);

namespace Enes5519\InventoryGUI\inventory;

use Enes5519\InventoryGUI\task\DoubleChestDelayTask;
use Enes5519\InventoryGUI\InventoryGUI;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory{

	public function getName() : string{
		return "Fake Double Chest";
	}

	public function getDefaultSize() : int{
		return 54;
	}

	public function sendFakeContainer(Player $player, Vector3 $pos, bool $force = false) : void{
		if(!$force){
			// HACK
			InventoryGUI::getAPI()->getScheduler()->scheduleDelayedTask(new DoubleChestDelayTask($this, $player, $pos, true), 5);
			return;
		}

		parent::sendFakeContainer($player, $pos);
	}

	public function sendFakeTile(Player $player, CompoundTag $tag, Vector3 $pos) : void{
		$writer = self::$nbtWriter ?? (self::$nbtWriter = new NetworkLittleEndianNBTStream());

		$tag->setInt(Chest::TAG_PAIRX, $pos->x + 1);
		$tag->setInt(Chest::TAG_PAIRZ, $pos->z);
		$pk = new BlockEntityDataPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->namedtag = $writer->write($tag);
		$player->dataPacket($pk);

		$tag->setInt(Chest::TAG_PAIRX, $pos->x);
		$tag->setInt(Chest::TAG_PAIRZ, $pos->z);
		$pk = new BlockEntityDataPacket();
		$pk->x = $pos->x + 1;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->namedtag = $writer->write($tag);
		$player->dataPacket($pk);
	}

	public function getRealBlocks(Player $player, Vector3 $pos) : array{
		return array_merge(
			parent::getRealBlocks($player, $pos),
			[$player->level->getBlockAt($pos->x + 1, $pos->y, $pos->z)]
		);
	}

	public function getFakeBlocks(Vector3 $pos) : array{
		return array_merge(
			parent::getFakeBlocks($pos),
			[$this->entry->getBlock()->setComponents($pos->x + 1, $pos->y, $pos->z)]
		);
	}

}