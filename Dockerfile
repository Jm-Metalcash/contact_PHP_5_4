# Utiliser Debian Jessie comme base
FROM debian:jessie-slim

# Configurer les sources pour l'archive Debian Jessie
RUN echo "deb http://archive.debian.org/debian jessie main contrib non-free" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security jessie/updates main contrib non-free" >> /etc/apt/sources.list && \
    echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99no-check-valid

# Mise à jour des paquets et installation de PHP5.4 et de GD
RUN apt-get update && \
    apt-get install -y --no-install-recommends --allow-unauthenticated \
    apache2 \
    php5 \
    libapache2-mod-php5 \
    php5-mysql \
    php5-gd \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Copier le fichier de configuration pour ServerName
COPY servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

# Copier les fichiers de l’application dans le dossier web d'Apache
COPY ./app /var/www/html

# Exposer le port 80
EXPOSE 80

# Lancer Apache en mode foreground
CMD ["apache2ctl", "-D", "FOREGROUND"]
