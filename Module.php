<?php declare(strict_types=1);
	/**
	 * Custom PowerDNS Module
	 *
	 * @copyright   Copyright (c) Apis Networks 2022
	 * @author      Matt Saladna (matt@apisnetworks.com)
	 * @license     MIT
	 */

	namespace apisnetworks\custom\powerdns;

	class Module extends \Opcenter\Dns\Providers\Powerdns\Module
	{
		// @var string IPv4 or IPv6 parking IP address
		const PARKING_IP = DNS_PARKING_IP;

		public function add_zone(string $domain, string $ip): bool
		{
			$ns = $this->get_hosting_nameservers($domain);
			if ($this->getParent($domain) || !$this->zone_exists($domain) ||
				\Net_Gethost::gethostbyname_t($domain, 5000, $ns) !== self::PARKING_IP)
			{
				return parent::add_zone($domain, $ip);
			}

			return $this->reset($domain);
		}

		/**
		 * Remove DNS zone from nameserver
		 *
		 * @param string $domain
		 *
		 * @return bool
		 */
		public function remove_zone_backend(string $domain): bool
		{
			if (!$this->zone_exists($domain)) {
				return true;
			}

			if (!$this->empty_zone($domain)) {
				return warn("Failed to truncate zone `%s'", $domain);
			}
			$ttl = MAX($this->min_ttl(), 30);
			foreach ((array)self::PARKING_IP as $ip) {
				$rr = false === strpos(self::PARKING_IP, ':') ? 'A' : 'AAAA';
				$this->add_record($domain, '', $rr, $ip, $ttl);
			}

			return true;
		}
	}
