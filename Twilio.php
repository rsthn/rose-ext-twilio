<?php
/*
**	Rose\Ext\Wind\Twilio
**
**	Copyright (c) 2021-2022, RedStar Technologies, All rights reserved.
**	https://rsthn.com/
**
**	THIS LIBRARY IS PROVIDED BY REDSTAR TECHNOLOGIES "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
**	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
**	PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL REDSTAR TECHNOLOGIES BE LIABLE FOR ANY
**	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
**	NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
**	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
**	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
**	USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace Rose\Ext\Wind;

use Rose\Errors\Error;
use Rose\Expr;
use Rose\Configuration;
use Rose\Map;

use Twilio\Rest\Client;

/**
 * Helper class.
 */
class TwilioHelper
{
	/**
	 * Loaded configuration.
	 */
	static $config_loaded = null;

	/**
	 * Current configuration.
	 */
	static $config = null;

	/**
	 * Ensures the configuration has been loaded.
	 * @param reset - Indicates if the current config should be set to the loaded one.
	 */
	static function ensureConfigReady ($reset=false)
	{
		if (self::$config_loaded === null)
			self::$config_loaded = Configuration::getInstance()->Twilio;

		if (self::$config === null)
		{
			self::$config = new Map();
			$reset = true;
		}

		if ($reset)
			self::$config->merge(self::$config_loaded, true);
	}

	/**
	 * Send an SMS message.
	 * @param toNumber: string
	 * @param message: string
	 * @param fromNumber?: string
	 * @return Map { sid:string, error:string, errorCode:number, price:number }
	 */
	static function sendSMS ($toNumber, $message, $fromNumber=null)
	{
		self::ensureConfigReady();

		try
		{
			$client = new Client (self::$config->sid, self::$config->token);

			$message = $client->messages->create(
				$toNumber,
				[
					'from' => $fromNumber === null ? self::$config->from : $fromNumber,
					'body' => $message
				]
			);

			return new Map([
				'sid' => $message->sid,
				'error' => $message->errorMessage,
				'errorCode' => $message->errorCode,
				'price' => $message->price
			]);
		}
		catch (\Exception $e)
		{
			return new Map([
				'error' => $e->getMessage(),
				'errorCode' => 409
			]);
		}
	}

	/**
	 * Sets configuration overrides.
	 */
	static function setConfig ($data)
	{
		if (\Rose\typeOf($data) !== 'Rose\\Map')
			return false;

		self::ensureConfigReady(true);

		$data->removeAll('/^$/');

		self::$config->merge($data, true);
		return true;
	}
};

/**
 * Sends an SMS message.
 * @param toNumber: string
 * @param message: string
 * @param fromNumber?: string
 * @return Map { sid:string, error:string, errorCode:number, price:number }
 */
Expr::register('twilio::send', function($args, $parts, $data)
{
	return TwilioHelper::sendSMS ($args->get(1), $args->get(2), $args->has(3) ? $args->get(3) : null);
});

/**
 * Sets configuration overrides.
 * @param data: Map
 * @return boolean
 */
Expr::register('twilio::config', function($args, $parts, $data)
{
	return TwilioHelper::setConfig ($args->get(1));
});
