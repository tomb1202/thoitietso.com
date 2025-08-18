module.exports = {
  apps: [
    {
      name: 'queue-district',
      script: 'artisan',
      args: 'queue:work --queue=districts --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 1,
      watch: false,
    },
    {
      name: 'queue-ward',
      script: 'artisan',
      args: 'queue:work --queue=wards --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 2,
      watch: false,
    },
    {
      name: 'queue-weather',
      script: 'artisan',
      args: 'queue:work --queue=weather --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 4,
      watch: false,
    }
  ]
};
