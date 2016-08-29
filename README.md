# SocialDev

This project is for the section 7 and 8 examples in Packt Publishing's PHP Projects Video Series.

To run you will need to have vagrant installed and working: https://www.vagrantup.com/

Then after you have cloned the repository execute

    vagrant up

Once your VM has provisioned, run

    vagrant ssh

Then on your VM, all code will be available under /var/www/social.dev. To begin running the example you can use the following commands

    cd /var/www/social.dev
    composer install
    ./update-schema.sh

Then you need to point the social.dev domain to your new VM. You can do this using either a tool such as dnsmasq or just with a simple line in your /etc/hosts file:

    192.168.56.101 beanstalkd.dev social.

The beanstalkd.dev domain is for another tool we will use later in the section.

Lastly, you will notice that each video has its own tag. So if you are viewing section 7 video 3, you can check out the tag v7.3. After checking out a new tag it is highly recommended you take the following steps:

    # On your host
    vagrant provision
    vagrant ssh
    
    # On your VM
    cd /var/www/social.dev
    composer install
    ./update-schema.sh
