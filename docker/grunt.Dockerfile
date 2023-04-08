FROM huli/grunt:alpine

RUN apk add gettext

# "grunt" is automatically prefixed by huli/grunt image
CMD ["compile"]