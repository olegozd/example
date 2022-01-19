pipeline {
    options {
        disableConcurrentBuilds()
    }
    agent {
        label 'argyle_lms_application_server_node'
    }

    environment {
        JENKINS_WORKSPACE_DIR = "${env.WORKSPACE}"
        APP_DIR = '/var/www/lms'
        COMPOSE_HTTP_TIMEOUT = 300
    }

    stages {
        stage('Gitlab pending') {
            steps {
                 echo 'Notify GitLab pending'
                 updateGitlabCommitStatus name: 'build', state: 'pending'
            }
        }

        stage('Copy code') {
            steps {
                 sh "cp -a ${JENKINS_WORKSPACE_DIR}/. ${APP_DIR}"
                 sh "ls ${APP_DIR}"
            }
        }

        stage('Setup environment') {
            steps {
                sh "cp ${APP_DIR}/.env.example ${APP_DIR}/.env"
                sh "cp ${APP_DIR}/docker/.env.dev ${APP_DIR}/docker/.env"
                sh "chmod 777 ${APP_DIR}/.env"
                sh "chmod 777 ${APP_DIR}/docker/.env"
                sh "env COMPOSE_HTTP_TIMEOUT=${COMPOSE_HTTP_TIMEOUT}"
            }
        }

        stage('Build application') {
            steps {
                sh "cd ${APP_DIR}/docker && docker-compose build"
                sh "cd ${APP_DIR}/docker && docker-compose up -d"
            }
        }

        stage('Update dependencies') {
            steps {
                sh 'docker exec argvlelms_workspace_1 composer install'
            }
        }

        stage('Post build application') {
            steps {
                sh 'docker exec argvlelms_workspace_1 chmod -R 777 storage/'
                sh 'docker exec argvlelms_workspace_1 php artisan key:generate'
                sh 'docker exec argvlelms_workspace_1 php artisan cache:forget spatie.permission.cache'
                sh 'docker exec argvlelms_workspace_1 php artisan config:clear'
                sh 'docker exec argvlelms_workspace_1 php artisan cache:clear'
                sh 'docker exec argvlelms_workspace_1 php artisan storage:link'
                sh 'docker exec argvlelms_workspace_1 php artisan migrate'
            }
        }

        stage('Gitlab success') {
            steps {
                echo 'Notify GitLab success'
                updateGitlabCommitStatus name: 'build', state: 'success'
            }
        }
    }
}

