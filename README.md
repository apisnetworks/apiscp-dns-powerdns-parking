# PowerDNS Parking

A sample module that sets the origin to a parking IP address upon deletion.

## Usage
1. Clone this repo.
```bash
cd /usr/local/apnscp
git clone https://github.com/apisnetworks/apiscp-dns-powerdns-parking config/custom/powerdns-parking
sudo -u apnscp ./composer dumpautoload
```

2. [Register](https://docs.apiscp.com/admin/DNS/#registering-custom-providers) the DNS provider, called "powerdns-parking" in `config/custom/boot.php`.
```php
\Opcenter\Dns::registerProvider('powerdns-parking', \apisnetworks\custom\powerdns\Module::class);
```

2. Set the parking IP in config.ini.
```bash
# Set parking IP
cpcmd scope:set cp.config dns parking_ip 1.2.3.4
# Fast-forward pending restart
systemctl restart apiscp
```

3. Replace "powerdns" with "powerdns-parking" for all applicable accounts. Iterate over a [collection](https://docs.apiscp.com/admin/cpcmd-examples/#collections) to do this quickly.
```bash
cpcmd -o json admin:collect '[]' '[dns.provider:powerdns]' | jq -r 'keys[]' | \
    while read -r SITE ; do 
	    EditDomain -c dns,provider=powerdns-parking $SITE
    done
```

## Notes
- Requires ApisCP v3.2.34 or later.
- Nameservers configured for PowerDNS must be valid. These are queried directly to determine parking IP presence.
- Parking IP is a single address.
- All records are emptied on deletion replacing the zone with a single IP address for the origin.
- Written as a work order. IP verification still uses newacct subdomain; this logic is adapted elsewhere.