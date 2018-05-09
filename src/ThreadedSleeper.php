<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\snooze;

/**
 * Notifiable Threaded class which tracks counts of notifications it receives.
 */
class ThreadedSleeper extends \Threaded{
	/**
	 * @var int
	 */
	private $notifCount = 0;

	/**
	 * Called from the main thread to wait for notifications, or until timeout.
	 *
	 * @param int $timeout defaults to 0 (no timeout, wait indefinitely)
	 */
	public function sleep(int $timeout = 0) : void{
		$this->synchronized(function(int $timeout) : void{
			if($this->notifCount === 0){
				$this->wait($timeout);
			}
		}, $timeout);
	}

	/**
	 * Call this from sleeper notifiers to wake up the main thread.
	 */
	public function wakeup() : void{
		$this->synchronized(function(){
			++$this->notifCount;
			$this->notify();
		});
	}

	/**
	 * Decreases pending notification count by the given number.
	 *
	 * @param int $notifCount
	 */
	public function clearNotifications(int $notifCount) : void{
		$this->notifCount -= $notifCount;
	}

	public function hasNotifications() : bool{
		//don't need to synchronize here, pthreads automatically locks/unlocks
		return $this->notifCount > 0;
	}
}
