module.exports = {
  apps: [
    {
      name: 'district',
      script: 'artisan',
      args: 'queue:work --queue=district --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 3,
      watch: false,
    },
    {
      name: 'ward',
      script: 'artisan',
      args: 'queue:work --queue=ward --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 3,
      watch: false,
    },
    {
      name: 'weather',
      script: 'artisan',
      args: 'queue:work --queue=weather --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 4,
      watch: false,
    }
  ]
};
