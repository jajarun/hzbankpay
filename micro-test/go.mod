module micro-test

go 1.15

require (
	github.com/Microsoft/go-winio v0.6.0 // indirect
	github.com/ProtonMail/go-crypto v0.0.0-20230411080316-8b3893ee7fca // indirect
	github.com/armon/go-metrics v0.4.1 // indirect
	github.com/asim/go-micro/plugins/registry/consul/v3 v3.7.0 // indirect
	github.com/asim/go-micro/v3 v3.7.1
	github.com/cloudflare/circl v1.3.2 // indirect
	github.com/fatih/color v1.15.0 // indirect
	github.com/fsnotify/fsnotify v1.6.0 // indirect
	github.com/go-git/go-git/v5 v5.6.1 // indirect
	github.com/golang/protobuf v1.5.3
	github.com/google/uuid v1.3.0 // indirect
	github.com/hashicorp/consul/api v1.20.0 // indirect
	github.com/hashicorp/go-cleanhttp v0.5.2 // indirect
	github.com/hashicorp/go-hclog v1.5.0 // indirect
	github.com/hashicorp/go-immutable-radix v1.3.1 // indirect
	github.com/imdario/mergo v0.3.15 // indirect
	github.com/mattn/go-isatty v0.0.18 // indirect
	github.com/miekg/dns v1.1.53 // indirect
	github.com/mitchellh/mapstructure v1.5.0 // indirect
	github.com/sergi/go-diff v1.3.1 // indirect
	github.com/urfave/cli/v2 v2.25.1 // indirect
	golang.org/x/crypto v0.8.0 // indirect
	golang.org/x/tools v0.8.0 // indirect
	google.golang.org/protobuf v1.30.0
)

// This can be removed once etcd becomes go gettable, version 3.4 and 3.5 is not,
// see https://github.com/etcd-io/etcd/issues/11154 and https://github.com/etcd-io/etcd/issues/11931.
replace google.golang.org/grpc => google.golang.org/grpc v1.26.0
