module.exports = {
  apps: [
    {
      name: 'default',
      script: 'artisan',
      args: 'queue:work --queue=default --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 3,
      watch: false,
    },
    {
      name: 'province',
      script: 'artisan',
      args: 'queue:work --queue=province --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 3,
      watch: false,
    },
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
    },
    {
      name: 'air',
      script: 'artisan',
      args: 'queue:work --queue=air --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 2,
      watch: false,
    },
    {
      name: 'news',
      script: 'artisan',
      args: 'queue:work --queue=news --sleep=1 --timeout=300 --tries=3',
      interpreter: 'php',
      instances: 2,
      watch: false,
    }
  ]
};
